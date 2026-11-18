<?php

namespace App\Services;

use App\Enums\GenerationStatus;
use App\Enums\WalletTransactionType;
use App\Models\Generation;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Support\Credits;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * On submit the maximum estimate is held. When the generation finishes, whatever was not
 * actually charged is returned, so a failed generation gets the whole hold back.
 */
class WalletService
{
    private const PER_PAGE = 20;

    /**
     * A generation is listed once, through its hold, and only after it finished with a charge.
     * Its hold plus its refund always equals the price charged, so the resource shows that price.
     *
     * @return LengthAwarePaginator<int, WalletTransaction>
     */
    public function collection(User $user): LengthAwarePaginator
    {
        return $user->walletTransactions()
            ->with('generation:id,actual_price,completed_at')
            ->where(fn (Builder $query) => $query
                ->whereNull('generation_id')
                ->orWhere(fn (Builder $holdQuery) => $holdQuery
                    ->where('type', WalletTransactionType::Hold)
                    ->whereHas('generation', fn (Builder $generationQuery) => $generationQuery
                        ->whereNotIn('status', GenerationStatus::unfinished())
                        ->where('actual_price', '>', 0))))
            ->latest()
            ->latest('id')
            ->paginate(self::PER_PAGE);
    }

    public function grantStartingCredits(User $user): void
    {
        $this->addCredits($user, WalletTransactionType::StartingCredit, Credits::fromDecimal(config('site.starting_credits')));
    }

    public function purchase(User $user, string $amount): void
    {
        $this->addCredits($user, WalletTransactionType::Purchase, Credits::fromDecimal($amount));
    }

    /**
     * A single conditional update, so two submissions at the same moment cannot both spend
     * the same credits. Call inside the transaction that creates the generation.
     */
    public function hold(User $user, Generation $generation, int $amount): bool
    {
        $isHeld = User::query()
            ->whereKey($user->id)
            ->where('balance', '>=', $amount)
            ->decrement('balance', $amount) === 1;

        if (! $isHeld) {
            return false;
        }

        $user->walletTransactions()->create([
            'generation_id' => $generation->id,
            'type' => WalletTransactionType::Hold,
            'amount' => -$amount,
        ]);

        return true;
    }

    /**
     * Called once, in the same transaction that moves the generation to a final status.
     * The unique index on generation and type also stops a second refund.
     */
    public function refundUnusedHold(Generation $generation): void
    {
        $hold = WalletTransaction::query()
            ->where('generation_id', $generation->id)
            ->where('type', WalletTransactionType::Hold)
            ->first();

        if ($hold === null) {
            return;
        }

        $unusedAmount = -$hold->amount - $generation->actual_price;

        if ($unusedAmount === 0) {
            return;
        }

        WalletTransaction::create([
            'user_id' => $hold->user_id,
            'generation_id' => $generation->id,
            'type' => WalletTransactionType::Refund,
            'amount' => $unusedAmount,
        ]);

        User::query()->whereKey($hold->user_id)->increment('balance', $unusedAmount);
    }

    private function addCredits(User $user, WalletTransactionType $type, int $amount): void
    {
        DB::transaction(function () use ($user, $type, $amount) {
            User::query()->whereKey($user->id)->increment('balance', $amount);

            $user->walletTransactions()->create(['type' => $type, 'amount' => $amount]);
        });
    }
}

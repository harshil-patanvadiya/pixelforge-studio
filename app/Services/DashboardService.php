<?php

namespace App\Services;

use App\Enums\GenerationStatus;
use App\Enums\UserRole;
use App\Enums\WalletTransactionType;
use App\Models\Generation;
use App\Models\User;
use App\Support\Credits;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(private PixelforgeService $pixelforgeService) {}

    /**
     * @return array{
     *     images_generated: int,
     *     failed: int,
     *     in_progress: int,
     *     spent: string,
     *     added: string
     * }
     */
    public function userStats(User $user): array
    {
        $added = $user->walletTransactions()
            ->whereIn('type', [WalletTransactionType::StartingCredit, WalletTransactionType::Purchase])
            ->sum('amount');

        return [
            'images_generated' => $user->generations()->where('status', GenerationStatus::Succeeded)->count(),
            'failed' => $user->generations()->where('status', GenerationStatus::Failed)->count(),
            'in_progress' => $user->generations()->whereIn('status', GenerationStatus::unfinished())->count(),
            'spent' => Credits::toDecimal((int) $user->generations()->sum('actual_price')),
            'added' => Credits::toDecimal((int) $added),
        ];
    }

    /**
     * Everything is grouped by the UTC day a generation completed, because that is the day
     * Pixelforge bills it on, so our provider costs can be compared with GET /v1/usage.
     *
     * @return array<string, mixed>
     */
    public function adminReport(string $startDate, string $endDate): array
    {
        $generations = $this->generationsCompletedBetween($startDate, $endDate);

        $generationsByDay = $generations->groupBy(fn (Generation $generation) => $generation->completed_at->toDateString());
        $pixelforgeCosts = $this->pixelforgeDailyCosts($startDate, $endDate);

        $days = collect(CarbonPeriod::create($startDate, $endDate))
            ->map(fn (CarbonInterface $day) => $this->dailyLine(
                $day->toDateString(),
                $generationsByDay->get($day->toDateString(), collect()),
                $pixelforgeCosts,
            ))
            ->reverse()
            ->values();

        return [
            'totals' => $this->withMoneyFormatted($this->totals($days)),
            'days' => $days->map(fn (array $day) => $this->withMoneyFormatted($day)),
            'has_pixelforge_usage' => $pixelforgeCosts !== null,
        ];
    }

    /**
     * Uses the same UTC completion day grouping as adminReport() so both pages add up to the same totals.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function userReport(string $startDate, string $endDate): Collection
    {
        $generationsByUser = $this->generationsCompletedBetween($startDate, $endDate)->groupBy('user_id');

        return User::role(UserRole::User->value)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'balance'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'balance' => Credits::toDecimal($user->balance),
                ...$this->sumGenerations($generationsByUser->get($user->id, collect())),
            ])
            ->sortByDesc('charges')
            ->values()
            ->map(fn (array $user) => $this->withMoneyFormatted($user));
    }

    /** @return Collection<int, Generation> */
    private function generationsCompletedBetween(string $startDate, string $endDate): Collection
    {
        return Generation::query()
            ->with('user:id,name,email,balance')
            ->whereBetween('completed_at', [
                Carbon::parse($startDate, 'UTC')->startOfDay(),
                Carbon::parse($endDate, 'UTC')->endOfDay(),
            ])
            ->get(['id', 'user_id', 'status', 'provider_cost', 'actual_price', 'completed_at']);
    }

    /**
     * @param  Collection<int, Generation>  $generations
     * @return array{generations: int, succeeded: int, failed: int, provider_cost: int, charges: int}
     */
    private function sumGenerations(Collection $generations): array
    {
        return [
            'generations' => $generations->count(),
            'succeeded' => $generations->where('status', GenerationStatus::Succeeded)->count(),
            'failed' => $generations->where('status', GenerationStatus::Failed)->count(),
            'provider_cost' => (int) $generations->sum('provider_cost'),
            'charges' => (int) $generations->sum('actual_price'),
        ];
    }

    /**
     * @param  Collection<int, Generation>  $generations
     * @param  array<string, int>|null  $pixelforgeCosts
     * @return array<string, mixed>
     */
    private function dailyLine(string $day, Collection $generations, ?array $pixelforgeCosts): array
    {
        $totals = $this->sumGenerations($generations);
        $pixelforgeCost = $pixelforgeCosts[$day] ?? null;

        return [
            'day' => $day,
            ...$totals,
            'pixelforge_cost' => $pixelforgeCost,
            'has_mismatch' => $pixelforgeCost !== null && $pixelforgeCost !== $totals['provider_cost'],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $days
     * @return array<string, mixed>
     */
    private function totals(Collection $days): array
    {
        return [
            'registered_users' => User::role(UserRole::User->value)->count(),
            'generations' => $days->sum('generations'),
            'succeeded' => $days->sum('succeeded'),
            'failed' => $days->sum('failed'),
            'provider_cost' => $days->sum('provider_cost'),
            'charges' => $days->sum('charges'),
            'pixelforge_cost' => $days->every(fn (array $day) => $day['pixelforge_cost'] !== null)
                ? $days->sum('pixelforge_cost')
                : null,
        ];
    }

    /** @return array<string, int>|null */
    private function pixelforgeDailyCosts(string $startDate, string $endDate): ?array
    {
        try {
            $usage = $this->pixelforgeService->usage($startDate, $endDate);
        } catch (RequestException|ConnectionException) {
            return null;
        }

        return collect($usage['data'])
            ->mapWithKeys(fn (array $day) => [$day['date'] => Credits::fromDecimal($day['cost_usd'])])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array<string, mixed>
     */
    private function withMoneyFormatted(array $line): array
    {
        $pixelforgeCost = $line['pixelforge_cost'] ?? null;

        return [
            ...$line,
            'provider_cost' => Credits::toDecimal($line['provider_cost']),
            'charges' => Credits::toDecimal($line['charges']),
            'profit' => Credits::toDecimal($line['charges'] - $line['provider_cost']),
            'pixelforge_cost' => $pixelforgeCost === null ? null : Credits::toDecimal($pixelforgeCost),
        ];
    }
}

<?php

namespace App\Services;

use App\Enums\GenerationStatus;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use App\Jobs\SendGenerationToPixelforge;
use App\Models\Generation;
use App\Models\User;
use App\Support\Credits;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GenerationService
{
    private const PER_PAGE = 12;

    public function __construct(
        private PixelforgeService $pixelforgeService,
        private PricingService $pricingService,
        private WalletService $walletService,
    ) {}

    /** @return LengthAwarePaginator<int, Generation> */
    public function collection(User $user): LengthAwarePaginator
    {
        return $user->generations()->latest()->latest('id')->paginate(self::PER_PAGE);
    }

    /**
     * Pixelforge cost per token type, then the markup, so users can see how the price was reached.
     *
     * @return array{
     *     rows: list<array{token_type: string, tokens: int, cost: string}>,
     *     provider_cost: string,
     *     markup: string,
     *     total: string
     * }|null
     */
    public function costBreakdown(Generation $generation): ?array
    {
        if ($generation->status !== GenerationStatus::Succeeded) {
            return null;
        }

        $usage = $generation->only(PricingService::TOKEN_TYPES);
        $costs = $this->pricingService->costPerTokenType($usage, $generation->rates);

        $rows = collect(PricingService::TOKEN_TYPES)->map(fn (string $tokenType) => [
            'token_type' => str_replace('_', ' ', ucfirst($tokenType)),
            'tokens' => $usage[$tokenType],
            'cost' => Credits::toDecimal($costs[$tokenType]),
        ])->all();

        return [
            'rows' => $rows,
            'provider_cost' => Credits::toDecimal($generation->provider_cost),
            'markup' => Credits::toDecimal($generation->actual_price - $generation->provider_cost),
            'total' => Credits::toDecimal($generation->actual_price),
        ];
    }

    /**
     * @param array{
     *     prompt: string,
     *     size: string,
     *     quality: string,
     *     reference_images?: list<string>|null
     * } $inputs
     */
    public function store(User $user, array $inputs): Generation
    {
        $referenceImages = $inputs['reference_images'] ?? [];

        $estimate = $this->pricingService->estimate(
            $inputs['prompt'],
            ImageSize::from($inputs['size']),
            ImageQuality::from($inputs['quality']),
            count($referenceImages),
            $this->pixelforgeService->pricing(),
        );

        $generation = DB::transaction(function () use ($user, $inputs, $referenceImages, $estimate) {
            $generation = $user->generations()->create([
                'reference' => (string) Str::uuid(),
                'prompt' => $inputs['prompt'],
                'size' => $inputs['size'],
                'quality' => $inputs['quality'],
                'reference_images' => $referenceImages,
                'status' => GenerationStatus::Pending,
                'estimated_typical_price' => $estimate['typical']['price'],
                'estimated_maximum_price' => $estimate['maximum']['price'],
            ]);

            if (! $this->walletService->hold($user, $generation, $estimate['maximum']['price'])) {
                throw ValidationException::withMessages([
                    'balance' => __('message.insufficientBalance'),
                ]);
            }

            return $generation;
        });

        SendGenerationToPixelforge::dispatch($generation);

        return $generation;
    }

    /**
     * @param array{
     *     prompt?: string|null,
     *     size: string,
     *     quality: string,
     *     reference_images?: list<string>|null
     * } $inputs
     * @return array{
     *     typical_price: string,
     *     maximum_price: string,
     *     can_afford: bool
     * }
     */
    public function estimate(User $user, array $inputs): array
    {
        $estimate = $this->pricingService->estimate(
            $inputs['prompt'] ?? '',
            ImageSize::from($inputs['size']),
            ImageQuality::from($inputs['quality']),
            count($inputs['reference_images'] ?? []),
            $this->pixelforgeService->pricing(),
        );

        return [
            'typical_price' => Credits::toDecimal($estimate['typical']['price']),
            'maximum_price' => Credits::toDecimal($estimate['maximum']['price']),
            'can_afford' => $user->balance >= $estimate['maximum']['price'],
        ];
    }

    /**
     * Create responses, polling and webhooks all come through here, so the forward only and
     * charge once rules hold whichever of them arrives first or twice.
     *
     * @param  array<string, mixed>  $pixelforgeGeneration
     */
    public function updateFromPixelforge(Generation $generation, array $pixelforgeGeneration): void
    {
        $this->saveProviderDetails($generation, $pixelforgeGeneration);

        $status = GenerationStatus::from($pixelforgeGeneration['status']);
        $errorCode = $pixelforgeGeneration['error']['code'] ?? null;

        $changes = match ($status) {
            GenerationStatus::Succeeded => $this->successChanges($pixelforgeGeneration),
            GenerationStatus::Failed => $this->failureChanges(
                $errorCode ?? 'generation_failed',
                $errorCode === 'internal_error' ? __('message.pixelforgeInternalError') : __('message.generationFailed'),
                $pixelforgeGeneration['completed_at'] ?? null,
            ),
            default => [],
        };

        $changes['started_at'] = $this->parseTime($pixelforgeGeneration['started_at'] ?? null);

        $this->moveToStatus($generation, $status, $changes);
    }

    public function markFailed(Generation $generation, string $errorCode, string $errorMessage): void
    {
        $this->moveToStatus($generation, GenerationStatus::Failed, $this->failureChanges($errorCode, $errorMessage, null));
    }

    /**
     * The update only matches while the generation is still in an earlier status, so a repeated
     * success finds nothing to update and the wallet can never be settled twice.
     *
     * @param  array<string, mixed>  $changes
     */
    private function moveToStatus(Generation $generation, GenerationStatus $status, array $changes): void
    {
        DB::transaction(function () use ($generation, $status, $changes) {
            $updatedRows = Generation::query()
                ->whereKey($generation->id)
                ->whereIn('status', $status->allowedPredecessors())
                ->update([...$changes, 'status' => $status, 'updated_at' => now()]);

            if ($updatedRows === 1 && $status->isFinal()) {
                $this->walletService->refundUnusedHold($generation->refresh());
            }
        });

        $generation->refresh();
    }

    /** @param array<string, mixed> $pixelforgeGeneration */
    private function saveProviderDetails(Generation $generation, array $pixelforgeGeneration): void
    {
        if ($generation->pixelforge_id !== null) {
            return;
        }

        $generation->forceFill([
            'pixelforge_id' => $pixelforgeGeneration['id'],
            'rates' => $pixelforgeGeneration['rates'],
            'provider_created_at' => $this->parseTime($pixelforgeGeneration['created_at'] ?? null),
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $pixelforgeGeneration
     * @return array<string, mixed>
     */
    private function successChanges(array $pixelforgeGeneration): array
    {
        $usage = $pixelforgeGeneration['usage'];
        $providerCost = $this->pricingService->providerCost($usage, $pixelforgeGeneration['rates']);

        return [
            'input_text_tokens' => $usage['input_text_tokens'],
            'input_image_tokens' => $usage['input_image_tokens'],
            'output_tokens' => $usage['output_tokens'],
            'reasoning_tokens' => $usage['reasoning_tokens'],
            'provider_cost' => $providerCost,
            'actual_price' => $this->pricingService->customerPrice($providerCost),
            'image_url' => $pixelforgeGeneration['image_url'],
            'image_url_expires_at' => $this->parseTime($pixelforgeGeneration['image_url_expires_at'] ?? null),
            'completed_at' => $this->parseTime($pixelforgeGeneration['completed_at'] ?? null),
        ];
    }

    /** @return array<string, mixed> */
    private function failureChanges(string $errorCode, string $errorMessage, ?string $completedAt): array
    {
        return [
            'provider_cost' => 0,
            'actual_price' => 0,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'completed_at' => $this->parseTime($completedAt) ?? now(),
        ];
    }

    private function parseTime(?string $time): ?Carbon
    {
        return $time === null ? null : Carbon::parse($time);
    }
}

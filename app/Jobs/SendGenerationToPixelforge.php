<?php

namespace App\Jobs;

use App\Models\Generation;
use App\Services\GenerationService;
use App\Services\PixelforgeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendGenerationToPixelforge implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [10, 20, 40, 80];

    private const RETRYABLE_CODES = [
        'connection_failed',
        'rate_limit_exceeded',
        'concurrency_limit_exceeded',
        'overloaded',
    ];

    private const USER_FACING_CODES = [
        'daily_limit_exceeded',
        'content_policy_violation',
        'validation_failed',
    ];

    public function __construct(public Generation $generation) {}

    public function handle(PixelforgeService $pixelforgeService, GenerationService $generationService): void
    {
        if ($this->generation->status->isFinal()) {
            return;
        }

        $this->generation->increment('submission_attempts');

        try {
            $pixelforgeGeneration = $pixelforgeService->createGeneration($this->payload(), $this->generation->reference);
        } catch (RequestException|ConnectionException $exception) {
            $this->handleFailure($exception, $generationService);

            return;
        }

        $generationService->updateFromPixelforge($this->generation, $pixelforgeGeneration);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Generation {$this->generation->id} could not be submitted.", ['exception' => $exception]);

        app(GenerationService::class)->markFailed(
            $this->generation,
            'submission_error',
            __('message.generationSubmissionFailed'),
        );
    }

    private function handleFailure(RequestException|ConnectionException $exception, GenerationService $generationService): void
    {
        $errorCode = $this->errorCode($exception);
        $isRetryable = $this->isRetryable($exception, $errorCode);

        if ($isRetryable && $this->attempts() < $this->tries) {
            $this->release($this->backoff[$this->attempts() - 1]);

            return;
        }

        if (! $isRetryable && ! in_array($errorCode, self::USER_FACING_CODES, true)) {
            Log::error("Generation {$this->generation->id} was rejected by Pixelforge.", ['error' => $exception->getMessage()]);
        }

        $generationService->markFailed(
            $this->generation,
            $errorCode,
            $isRetryable ? __('message.pixelforgeBusy') : $this->userMessage($exception, $errorCode),
        );
    }

    private function errorCode(RequestException|ConnectionException $exception): string
    {
        if ($exception instanceof ConnectionException) {
            return 'connection_failed';
        }

        return (string) $exception->response->json('error.code', 'unknown_error');
    }

    private function isRetryable(RequestException|ConnectionException $exception, string $errorCode): bool
    {
        if (in_array($errorCode, self::RETRYABLE_CODES, true)) {
            return true;
        }

        return $exception instanceof RequestException && $exception->response->serverError();
    }

    /** Only reached for non retryable failures, which always carry a response. */
    private function userMessage(RequestException $exception, string $errorCode): string
    {
        return match ($errorCode) {
            'daily_limit_exceeded' => __('message.dailyLimitExceeded'),
            'content_policy_violation' => __('message.contentPolicyViolation'),
            'validation_failed' => __('message.pixelforgeValidationFailed', [
                'message' => $exception->response->json('error.message', "Pixelforge returned HTTP {$exception->response->status()}."),
            ]),
            default => __('message.generationSubmissionFailed'),
        };
    }

    /**
     * @return array{
     *     prompt: string,
     *     size: string,
     *     quality: string,
     *     reference_images: list<string>,
     *     metadata: array<string, string>
     * }
     */
    private function payload(): array
    {
        return [
            'prompt' => $this->generation->prompt,
            'size' => $this->generation->size->value,
            'quality' => $this->generation->quality->value,
            'reference_images' => $this->generation->reference_images,
            'metadata' => ['reference' => $this->generation->reference],
        ];
    }
}

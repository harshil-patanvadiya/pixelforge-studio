<?php

namespace App\Jobs;

use App\Enums\GenerationStatus;
use App\Models\Generation;
use App\Services\GenerationService;
use App\Services\PixelforgeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * Fallback for webhooks that never arrive. Pixelforge allows 60 requests a minute across the
 * account, so each run checks a small batch.
 */
class PollUnfinishedGenerations implements ShouldQueue
{
    use Queueable;

    private const MINUTES_BEFORE_POLLING = 2;

    private const BATCH_SIZE = 20;

    public function handle(PixelforgeService $pixelforgeService, GenerationService $generationService): void
    {
        $staleGenerations = Generation::query()
            ->whereIn('status', [GenerationStatus::Queued->value, GenerationStatus::Processing->value])
            ->where('created_at', '<=', now()->subMinutes(self::MINUTES_BEFORE_POLLING))
            ->oldest()
            ->limit(self::BATCH_SIZE)
            ->get();

        $staleGenerations->each(function (Generation $generation) use ($pixelforgeService, $generationService) {
            try {
                $generationService->updateFromPixelforge($generation, $pixelforgeService->findGeneration($generation->pixelforge_id));
            } catch (RequestException|ConnectionException $exception) {
                Log::warning("Polling generation {$generation->id} failed.", ['error' => $exception->getMessage()]);
            }
        });

        Log::info("Polled {$staleGenerations->count()} unfinished generations.");
    }
}

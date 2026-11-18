<?php

namespace App\Jobs;

use App\Models\Generation;
use App\Models\WebhookEvent;
use App\Services\GenerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessWebhookEvent implements ShouldQueue
{
    use Queueable;

    public function __construct(public WebhookEvent $webhookEvent) {}

    public function handle(GenerationService $generationService): void
    {
        $pixelforgeGeneration = $this->webhookEvent->payload['data']['object'] ?? null;

        if (($pixelforgeGeneration['object'] ?? null) !== 'generation') {
            $this->webhookEvent->update(['processed_at' => now()]);

            return;
        }

        $generation = $this->findGeneration($pixelforgeGeneration);

        if ($generation === null) {
            Log::warning("Webhook event {$this->webhookEvent->event_id} matches no generation.");
            $this->webhookEvent->update(['processed_at' => now()]);

            return;
        }

        $generationService->updateFromPixelforge($generation, $pixelforgeGeneration);

        $this->webhookEvent->update(['generation_id' => $generation->id, 'processed_at' => now()]);
    }

    /**
     * Matching on our own reference works even when the webhook beats the create response,
     * before the Pixelforge id has been stored.
     *
     * @param  array<string, mixed>  $pixelforgeGeneration
     */
    private function findGeneration(array $pixelforgeGeneration): ?Generation
    {
        $reference = $pixelforgeGeneration['metadata']['reference'] ?? null;

        if ($reference !== null) {
            return Generation::firstWhere('reference', $reference);
        }

        return Generation::firstWhere('pixelforge_id', $pixelforgeGeneration['id']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use App\Services\PixelforgeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PixelforgeWebhookController extends Controller
{
    public function __construct(private PixelforgeService $pixelforgeService) {}

    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();

        if (! $this->pixelforgeService->isValidSignature($payload, (string) $request->header('Pixelforge-Signature'))) {
            abort(400, __('message.invalidSignature'));
        }

        $event = json_decode($payload, true);

        $webhookEvent = WebhookEvent::createOrFirst(
            ['event_id' => $event['id']],
            ['type' => $event['type'], 'payload' => $event],
        );

        if ($webhookEvent->wasRecentlyCreated) {
            ProcessWebhookEvent::dispatch($webhookEvent);
        }

        return response()->noContent();
    }
}

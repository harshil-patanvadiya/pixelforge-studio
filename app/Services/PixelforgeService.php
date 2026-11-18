<?php

namespace App\Services;

use App\Exceptions\CustomErrorException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PixelforgeService
{
    private const PRICING_CACHE_SECONDS = 300;

    private const SIGNATURE_TOLERANCE_SECONDS = 300;

    /** @return array<string, mixed> */
    public function pricing(): array
    {
        try {
            return Cache::remember('pixelforge.pricing', self::PRICING_CACHE_SECONDS, fn () => $this->request()->get('pricing')->json());
        } catch (RequestException|ConnectionException $exception) {
            throw new CustomErrorException(__('message.pixelforgeUnavailable'), previous: $exception);
        }
    }

    /**
     * @param array{
     *     prompt: string,
     *     size: string,
     *     quality: string,
     *     reference_images: list<string>,
     *     metadata: array<string, string>
     * } $payload
     * @return array<string, mixed>
     */
    public function createGeneration(array $payload, string $idempotencyKey): array
    {
        return $this->authenticatedRequest()
            ->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->post('generations', $payload)
            ->json();
    }

    /** @return array<string, mixed> */
    public function findGeneration(string $pixelforgeId): array
    {
        return $this->authenticatedRequest()->get("generations/{$pixelforgeId}")->json();
    }

    /** @return array<string, mixed> */
    public function usage(string $startDate, string $endDate): array
    {
        return $this->authenticatedRequest()->get('usage', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ])->json();
    }

    public function isValidSignature(string $payload, string $signatureHeader): bool
    {
        $secret = config('site.pixelforge.webhook_secret');

        if (blank($secret)) {
            return false;
        }

        $parts = collect(explode(',', $signatureHeader))
            ->mapWithKeys(function (string $part) {
                [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');

                return [$key => $value];
            });

        $timestamp = (int) $parts->get('t', 0);

        if (abs(time() - $timestamp) > self::SIGNATURE_TOLERANCE_SECONDS) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', "{$timestamp}.{$payload}", $secret), (string) $parts->get('v1', ''));
    }

    private function authenticatedRequest(): PendingRequest
    {
        return $this->request()->withToken((string) config('site.pixelforge.api_key'));
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(config('site.pixelforge.base_url'))
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->connectTimeout(5)
            ->throw();
    }
}

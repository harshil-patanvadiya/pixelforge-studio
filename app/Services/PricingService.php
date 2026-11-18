<?php

namespace App\Services;

use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use App\Support\Credits;
use UnexpectedValueException;

/**
 * All amounts are integer nanodollars. Rates are USD per 1M tokens as decimal strings.
 */
class PricingService
{
    public const TOKEN_TYPES = [
        'input_text_tokens',
        'input_image_tokens',
        'output_tokens',
        'reasoning_tokens',
    ];

    private const TOKENS_PER_RATE_UNIT = 1_000_000;

    private const BASIS_POINTS = 10_000;

    /**
     * @param array{
     *     rates: array<string, string>,
     *     tokens: array{
     *         characters_per_text_token: int,
     *         text_token_variance: float,
     *         per_reference_image: int,
     *         output: array<string, array<string, int>>,
     *         reasoning: array<string, array{min: int, max: int}>
     *     }
     * } $pricing
     * @return array{
     *     typical: array{tokens: array<string, int>, provider_cost: int, price: int},
     *     maximum: array{tokens: array<string, int>, provider_cost: int, price: int}
     * }
     */
    public function estimate(string $prompt, ImageSize $size, ImageQuality $quality, int $referenceImageCount, array $pricing): array
    {
        $tokenRules = $pricing['tokens'];

        $expectedTextTokens = (int) ceil(mb_strlen($prompt) / $tokenRules['characters_per_text_token']);
        $maximumTextTokens = $this->applyVariance($expectedTextTokens, $tokenRules['text_token_variance']);
        $imageTokens = $referenceImageCount * $tokenRules['per_reference_image'];
        $outputTokens = $tokenRules['output'][$quality->value][$size->value];
        $reasoningRange = $tokenRules['reasoning'][$quality->value];

        $typicalTokens = [
            'input_text_tokens' => $expectedTextTokens,
            'input_image_tokens' => $imageTokens,
            'output_tokens' => $outputTokens,
            'reasoning_tokens' => intdiv($reasoningRange['min'] + $reasoningRange['max'], 2),
        ];

        $maximumTokens = [
            'input_text_tokens' => $maximumTextTokens,
            'input_image_tokens' => $imageTokens,
            'output_tokens' => $outputTokens,
            'reasoning_tokens' => $reasoningRange['max'],
        ];

        return [
            'typical' => $this->priceTokens($typicalTokens, $pricing['rates']),
            'maximum' => $this->priceTokens($maximumTokens, $pricing['rates']),
        ];
    }

    /**
     * @param  array<string, int>  $usage
     * @param  array<string, string>  $rates
     */
    public function providerCost(array $usage, array $rates): int
    {
        return collect(self::TOKEN_TYPES)->sum(
            fn (string $tokenType) => ($usage[$tokenType] ?? 0) * $this->costPerToken($rates, $tokenType),
        );
    }

    /**
     * @param  array<string, int>  $usage
     * @param  array<string, string>  $rates
     * @return array<string, int>
     */
    public function costPerTokenType(array $usage, array $rates): array
    {
        return collect(self::TOKEN_TYPES)
            ->mapWithKeys(fn (string $tokenType) => [
                $tokenType => ($usage[$tokenType] ?? 0) * $this->costPerToken($rates, $tokenType),
            ])
            ->all();
    }

    public function customerPrice(int $providerCost): int
    {
        $percentWithMarkup = 100 + config('site.markup_percent');

        return intdiv($providerCost * $percentWithMarkup + 99, 100);
    }

    /**
     * @param  array<string, int>  $tokens
     * @param  array<string, string>  $rates
     * @return array{tokens: array<string, int>, provider_cost: int, price: int}
     */
    private function priceTokens(array $tokens, array $rates): array
    {
        $providerCost = $this->providerCost($tokens, $rates);

        return [
            'tokens' => $tokens,
            'provider_cost' => $providerCost,
            'price' => $this->customerPrice($providerCost),
        ];
    }

    /**
     * @param  array<string, string>  $rates
     */
    private function costPerToken(array $rates, string $tokenType): int
    {
        if (! isset($rates[$tokenType])) {
            throw new UnexpectedValueException("Pixelforge rates are missing `{$tokenType}`.");
        }

        $costPerMillionTokens = Credits::fromDecimal($rates[$tokenType]);

        if ($costPerMillionTokens % self::TOKENS_PER_RATE_UNIT !== 0) {
            throw new UnexpectedValueException("Rate `{$rates[$tokenType]}` for `{$tokenType}` cannot be charged in whole nanodollars per token.");
        }

        return intdiv($costPerMillionTokens, self::TOKENS_PER_RATE_UNIT);
    }

    /**
     * The variance arrives as a JSON float (0.15). It is converted to basis points once
     * so the token arithmetic itself stays in integers.
     */
    private function applyVariance(int $tokens, float $variance): int
    {
        $varianceBasisPoints = (int) round($variance * self::BASIS_POINTS);

        $scaledTokens = $tokens * (self::BASIS_POINTS + $varianceBasisPoints);

        return intdiv($scaledTokens + self::BASIS_POINTS - 1, self::BASIS_POINTS);
    }
}

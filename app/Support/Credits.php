<?php

namespace App\Support;

use UnexpectedValueException;

/**
 * Every amount of money in the app (balances, prices, costs) is stored and calculated as an
 * integer count of nanodollars, where 1 USD = 1,000,000,000.
 *
 * Why integers: floats cannot hold most decimals exactly (0.1 + 0.2 !== 0.3), and the errors
 * pile up when we add many small charges. Integer maths is exact.
 *
 * Why nine decimals: Pixelforge reports costs with up to nine decimal places, so this is the
 * smallest unit that keeps its numbers exact and lets our totals match its usage report.
 *
 * Use fromDecimal() when money comes in (config, user input, the Pixelforge API) and
 * toDecimal() when it goes out (Inertia props, resources). Decimal strings are used at the
 * edges because they survive JSON without being turned into floats.
 *
 * Named after the credits users see in their wallet, but the same unit is used for every
 * amount, including Pixelforge costs and our profit.
 */
final class Credits
{
    public const PER_DOLLAR = 1_000_000_000;

    private const DECIMALS = 9;

    /** Turns a plain decimal string such as "0.0148375" into nanodollars. */
    public static function fromDecimal(string $amount): int
    {
        if (! preg_match('/^(\d+)(?:\.(\d+))?$/', $amount, $matches)) {
            throw new UnexpectedValueException("Amount `{$amount}` is not a plain decimal.");
        }

        $whole = $matches[1];
        $fraction = rtrim($matches[2] ?? '', '0');

        if (strlen($fraction) > self::DECIMALS) {
            throw new UnexpectedValueException("Amount `{$amount}` is more precise than a nanodollar.");
        }

        return (int) $whole * self::PER_DOLLAR + (int) str_pad($fraction, self::DECIMALS, '0');
    }

    /** Turns nanodollars back into a decimal string with all nine places, for example "-0.014837500". */
    public static function toDecimal(int $nanodollars): string
    {
        $sign = $nanodollars < 0 ? '-' : '';
        $absolute = abs($nanodollars);
        $whole = intdiv($absolute, self::PER_DOLLAR);
        $fraction = str_pad((string) ($absolute % self::PER_DOLLAR), self::DECIMALS, '0', STR_PAD_LEFT);

        return "{$sign}{$whole}.{$fraction}";
    }
}

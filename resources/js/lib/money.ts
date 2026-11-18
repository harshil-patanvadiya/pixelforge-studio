// Amounts arrive as exact 9-decimal strings. Formatting works on the string so no precision is lost.
export function formatUsd(amount: string | null): string {
    if (amount === null) {
        return '—';
    }

    if (amount.startsWith('-')) {
        return `-${formatUsd(amount.slice(1))}`;
    }

    const [whole, fraction = ''] = amount.split('.');
    const trimmedFraction = fraction.replace(/0+$/, '').padEnd(2, '0');

    return `$${whole}.${trimmedFraction}`;
}

const NANODOLLARS_PER_DOLLAR = BigInt(1_000_000_000);
const NANODOLLARS_PER_CENT = BigInt(10_000_000);

// Rounded for users: whole cents from one cent up, otherwise the first significant digit so tiny charges never read as $0.00.
export function formatCredits(amount: string | null): string {
    if (amount === null) {
        return '—';
    }

    if (amount.startsWith('-')) {
        return `-${formatCredits(amount.slice(1))}`;
    }

    const [whole, fraction = ''] = amount.split('.');
    const nanodollars = BigInt(whole) * NANODOLLARS_PER_DOLLAR + BigInt(fraction.padEnd(9, '0').slice(0, 9));

    if (nanodollars === BigInt(0)) {
        return '$0.00';
    }

    if (nanodollars >= NANODOLLARS_PER_CENT) {
        return formatCents(roundHalfUp(nanodollars, NANODOLLARS_PER_CENT) / NANODOLLARS_PER_CENT);
    }

    const firstDigitUnit = BigInt(10) ** BigInt(nanodollars.toString().length - 1);
    const rounded = roundHalfUp(nanodollars, firstDigitUnit);

    if (rounded >= NANODOLLARS_PER_CENT) {
        return formatCents(rounded / NANODOLLARS_PER_CENT);
    }

    return `$0.${rounded.toString().padStart(9, '0').replace(/0+$/, '')}`;
}

function roundHalfUp(value: bigint, unit: bigint): bigint {
    return ((value + unit / BigInt(2)) / unit) * unit;
}

function formatCents(cents: bigint): string {
    const centsPerDollar = BigInt(100);

    return `$${cents / centsPerDollar}.${(cents % centsPerDollar).toString().padStart(2, '0')}`;
}

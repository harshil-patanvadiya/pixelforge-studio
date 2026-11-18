<?php

namespace App\Enums;

enum WalletTransactionType: string
{
    case StartingCredit = 'starting_credit';
    case Purchase = 'purchase';
    case Hold = 'hold';
    case Refund = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::StartingCredit => 'Starting credits',
            self::Purchase => 'Credits bought',
            self::Hold => 'Held for generation',
            self::Refund => 'Returned from generation',
        };
    }
}

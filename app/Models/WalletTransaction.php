<?php

namespace App\Models;

use App\Enums\WalletTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Amount is signed integer nanodollars: positive adds to the balance, negative takes from it.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $generation_id
 * @property WalletTransactionType $type
 * @property int $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Generation|null $generation
 */
class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'generation_id',
        'type',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'type' => WalletTransactionType::class,
            'amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class);
    }
}

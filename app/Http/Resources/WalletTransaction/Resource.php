<?php

namespace App\Http\Resources\WalletTransaction;

use App\Models\WalletTransaction;
use App\Support\Credits;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WalletTransaction */
class Resource extends JsonResource
{
    /**
     * @return array{
     *     id: int,
     *     type: string,
     *     display_type: string,
     *     amount: string,
     *     generation_id: int|null,
     *     created_at: string
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'display_type' => $this->generation_id === null ? $this->type->label() : 'Used for generation',
            'amount' => Credits::toDecimal($this->generation_id === null ? $this->amount : -$this->generation->actual_price),
            'generation_id' => $this->generation_id,
            'created_at' => ($this->generation?->completed_at ?? $this->created_at)->toIso8601String(),
        ];
    }
}

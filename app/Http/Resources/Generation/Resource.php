<?php

namespace App\Http\Resources\Generation;

use App\Models\Generation;
use App\Support\Credits;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Generation */
class Resource extends JsonResource
{
    /**
     * @return array{
     *     id: int,
     *     prompt: string,
     *     size: string,
     *     display_size: string,
     *     quality: string,
     *     display_quality: string,
     *     status: string,
     *     display_status: string,
     *     is_finished: bool,
     *     reference_images: list<string>,
     *     estimated_typical_price: string,
     *     estimated_maximum_price: string,
     *     actual_price: string|null,
     *     image_url: string|null,
     *     has_expired_image: bool,
     *     error_message: string|null,
     *     created_at: string,
     *     completed_at: string|null
     * }
     */
    public function toArray(Request $request): array
    {
        $hasUsableImage = $this->hasUsableImage();

        return [
            'id' => $this->id,
            'prompt' => $this->prompt,
            'size' => $this->size->value,
            'display_size' => $this->size->label(),
            'quality' => $this->quality->value,
            'display_quality' => $this->quality->label(),
            'status' => $this->status->value,
            'display_status' => $this->status->label(),
            'is_finished' => $this->status->isFinal(),
            'reference_images' => $this->reference_images,
            'estimated_typical_price' => Credits::toDecimal($this->estimated_typical_price),
            'estimated_maximum_price' => Credits::toDecimal($this->estimated_maximum_price),
            'actual_price' => $this->actual_price === null ? null : Credits::toDecimal($this->actual_price),
            'image_url' => $hasUsableImage ? $this->image_url : null,
            'has_expired_image' => $this->image_url !== null && ! $hasUsableImage,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}

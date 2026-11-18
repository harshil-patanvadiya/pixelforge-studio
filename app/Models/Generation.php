<?php

namespace App\Models;

use App\Enums\GenerationStatus;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Money columns are integer nanodollars (1e-9 USD) so Pixelforge's nine decimal costs stay exact.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $reference
 * @property string|null $pixelforge_id
 * @property string $prompt
 * @property ImageSize $size
 * @property ImageQuality $quality
 * @property list<string> $reference_images
 * @property GenerationStatus $status
 * @property int $estimated_typical_price
 * @property int $estimated_maximum_price
 * @property array<string, string>|null $rates
 * @property int|null $input_text_tokens
 * @property int|null $input_image_tokens
 * @property int|null $output_tokens
 * @property int|null $reasoning_tokens
 * @property int|null $provider_cost
 * @property int|null $actual_price
 * @property string|null $image_url
 * @property Carbon|null $image_url_expires_at
 * @property string|null $error_code
 * @property string|null $error_message
 * @property int $submission_attempts
 * @property Carbon|null $provider_created_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $user
 */
class Generation extends Model
{
    protected $fillable = [
        'user_id',
        'reference',
        'prompt',
        'size',
        'quality',
        'reference_images',
        'status',
        'estimated_typical_price',
        'estimated_maximum_price',
    ];

    protected function casts(): array
    {
        return [
            'size' => ImageSize::class,
            'quality' => ImageQuality::class,
            'status' => GenerationStatus::class,
            'reference_images' => 'array',
            'rates' => 'array',
            'estimated_typical_price' => 'integer',
            'estimated_maximum_price' => 'integer',
            'input_text_tokens' => 'integer',
            'input_image_tokens' => 'integer',
            'output_tokens' => 'integer',
            'reasoning_tokens' => 'integer',
            'provider_cost' => 'integer',
            'actual_price' => 'integer',
            'submission_attempts' => 'integer',
            'image_url_expires_at' => 'datetime',
            'provider_created_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function webhookEvents(): HasMany
    {
        return $this->hasMany(WebhookEvent::class);
    }

    public function hasUsableImage(): bool
    {
        if ($this->image_url === null) {
            return false;
        }

        if ($this->image_url_expires_at === null) {
            return true;
        }

        return $this->image_url_expires_at->isFuture();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Generation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'original_image_url',
        'original_thumb_sm',
        'original_thumb_md',
        'processed_image_url',
        'processed_thumb_sm',
        'processed_thumb_md',
        'style_name',
        'prompt_used',
        'status',
        'cost_in_credits',
    ];

    // Relationship to the Mobile App User
    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }



    protected function originalImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value && !str_starts_with($value, 'http')
            ? Storage::disk('r2')->url($value)
            : $value
        );
    }

    protected function originalThumbMd(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value && !str_starts_with($value, 'http')
            ? Storage::disk('r2')->url($value)
            : $value
        );
    }

    protected function originalThumbSm(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value && !str_starts_with($value, 'http')
            ? Storage::disk('r2')->url($value)
            : $value
        );
    }

    protected function processedImageUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value)
                    return null;
                if (str_starts_with($value, 'http')) {
                    return $value;
                }
                return Storage::disk('r2')->url($value);
            }
        );
    }

    /**
     * Automatically generate full URL for Result MD Thumb
     */
    protected function processedThumbMd(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value && !str_starts_with($value, 'http')
            ? Storage::disk('r2')->url($value)
            : $value
        );
    }

    /**
     * Automatically generate full URL for Result SM Thumb
     */
    protected function processedThumbSm(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value && !str_starts_with($value, 'http')
            ? Storage::disk('r2')->url($value)
            : $value
        );
    }
    protected function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_name', 'title');
    }

}
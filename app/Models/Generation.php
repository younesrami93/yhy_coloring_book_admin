<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Generation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'original_image_url',
        'processed_image_url',
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
}
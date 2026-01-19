<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Style extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'prompt',
        'thumbnail_url',
        'example_before_url',
        'example_after_url',
        'usage_count',
        'is_active',
    ];
}

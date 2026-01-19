<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'app_user_id',
        'device_uuid',
        'fcm_token',
        'platform',
        'language',
        'app_version'
    ];

    public function user()
    {
        return $this->belongsTo(AppUser::class);
    }
}
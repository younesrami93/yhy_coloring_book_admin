<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Important!

class AppUser extends Model
{
    use HasApiTokens, SoftDeletes, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'avatar_url',
        'social_id',
        'social_provider',
        'is_guest',
        'credits'
    ];

    // A user can have multiple devices (Phone + Tablet)
    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function routeNotificationForFcm()
    {
        // Get all device tokens that are not null
        return $this->devices()
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();
    }

}
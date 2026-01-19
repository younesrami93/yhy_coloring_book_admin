<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens; // Important!

class AppUser extends Model
{
    use HasApiTokens, SoftDeletes;

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
}
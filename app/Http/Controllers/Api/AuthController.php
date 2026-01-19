<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Handle Login (Guest or Social)
     */
    public function login(Request $request)
    {
        $request->validate([
            'device_uuid' => 'required|string', // Hardware ID
            'fcm_token'   => 'nullable|string',
            'platform'    => 'nullable|in:android,ios',
            'language'    => 'nullable|string|max:5',
            // Optional Social Fields
            'provider'    => 'nullable|in:google,facebook',
            'social_id'   => 'required_with:provider',
            'email'       => 'nullable|email',
            'name'        => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $user = null;

            // 1. Try to find via Social ID
            if ($request->has('social_id')) {
                $user = AppUser::where('social_id', $request->social_id)
                               ->where('social_provider', $request->provider)
                               ->first();

                // If not found, create new "Social User"
                if (!$user) {
                    $user = AppUser::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'social_id' => $request->social_id,
                        'social_provider' => $request->provider,
                        'is_guest' => false,
                        'avatar_url' => $request->avatar_url,
                    ]);
                }
            }
            
            // 2. If no social, try to find Guest via Device UUID
            // (Only if we didn't just log in via social)
            if (!$user) {
                // Check if this device is already linked to a user
                $device = Device::where('device_uuid', $request->device_uuid)->first();
                
                if ($device) {
                    $user = $device->user;
                } else {
                    // Create brand new Guest
                    $user = AppUser::create([
                        'name' => 'Guest-' . substr($request->device_uuid, 0, 6),
                        'is_guest' => true,
                    ]);
                }
            }

            // 3. Update or Create Device Record
            // We ensure this specific device UUID belongs to this user now
            Device::updateOrCreate(
                ['device_uuid' => $request->device_uuid],
                [
                    'app_user_id' => $user->id,
                    'fcm_token' => $request->fcm_token,
                    'platform' => $request->platform,
                    'language' => $request->language ?? 'en',
                    'app_version' => $request->app_version,
                ]
            );

            // 4. Issue Token
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
                'credits' => $user->credits,
            ]);
        });
    }

    /**
     * Get Current User Data
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'credits' => $request->user()->credits,
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite; // Make sure to import this

class AuthController extends Controller
{
    /**
     * Handle Login (Guest or Social)
     */


    public function login(Request $request)
    {
        $request->validate([
            'device_uuid' => 'required|string',
            'fcm_token' => 'nullable|string',
            'platform' => 'nullable|in:android,ios',
            'language' => 'nullable|string|max:5',
            'app_version' => 'nullable|string',
            'provider' => 'nullable|in:google,facebook',
            'social_token' => 'required_with:provider|string',
        ]);

        return DB::transaction(function () use ($request) {
            $user = null;

            // --- 1. HANDLE SOCIAL LOGIN ---
            if ($request->has('provider')) {
                try {
                    // Verify Token
                    $socialUser = Socialite::driver($request->provider)
                        ->stateless()
                        ->userFromToken($request->social_token);

                    $socialId = $socialUser->getId();
                    $email = $socialUser->getEmail();

                    // A. Check if this Social Account already exists
                    $user = AppUser::where('social_id', $socialId)
                        ->where('social_provider', $request->provider)
                        ->first();

                    // B. Link by Email (Prevent duplicates)
                    if (!$user && $email) {
                        $user = AppUser::where('email', $email)->first();
                        if ($user) {
                            // Link existing email user to this social provider
                            $user->update([
                                'social_id' => $socialId,
                                'social_provider' => $request->provider,
                                'avatar_url' => $user->avatar_url ?? $socialUser->getAvatar(),
                            ]);
                        }
                    }

                    // C. NEW: Convert Guest to Social User
                    // If the user doesn't exist yet, checking if they are currently a Guest
                    if (!$user) {
                        // Find the Guest User associated with this Device
                        $device = Device::where('device_uuid', $request->device_uuid)->first();

                        if ($device && $device->user) {
                            // CONVERT GUEST -> SOCIAL USER
                            $user = $device->user;
                            $user->update([
                                'name' => $socialUser->getName(),
                                'email' => $email,
                                'social_id' => $socialId,
                                'social_provider' => $request->provider,
                                'avatar_url' => $socialUser->getAvatar(),
                                'is_guest' => false, // No longer a guest!
                            ]);
                        } else {
                            // D. Create Brand New User (No guest found, new device)
                            $user = AppUser::create([
                                'name' => $socialUser->getName(),
                                'email' => $email,
                                'social_id' => $socialId,
                                'social_provider' => $request->provider,
                                'is_guest' => false,
                                'avatar_url' => $socialUser->getAvatar(),
                                'credits' => 3
                            ]);
                        }
                    }

                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Social login failed.',
                        'error' => $e->getMessage()
                    ], 401);
                }
            }

            // --- 2. HANDLE GUEST LOGIN (No Provider) ---
            if (!$user) {
                $device = Device::where('device_uuid', $request->device_uuid)->first();

                if ($device && $device->user) {
                    $user = $device->user;
                } else {
                    $user = AppUser::create([
                        'name' => 'Guest-' . substr($request->device_uuid, 0, 6),
                        'is_guest' => true,
                        'credits' => 3
                    ]);
                }
            }

            // --- 3. SYNC DEVICE DATA ---
            // Ensure this device is linked to the final determined user
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

            // Issue Token
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
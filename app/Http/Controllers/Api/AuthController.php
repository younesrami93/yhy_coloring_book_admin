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

            // ====================================================
            // 1. HANDLE SOCIAL LOGIN
            // ====================================================
            if ($request->has('provider')) {
                try {
                    $socialUser = Socialite::driver($request->provider)->stateless()->userFromToken($request->social_token);

                    // A. Check if Social Account ALREADY EXISTS
                    $user = AppUser::where('social_id', $socialUser->getId())
                        ->where('social_provider', $request->provider)
                        ->first();

                    // Fallback: Link by email
                    if (!$user && $socialUser->getEmail()) {
                        $user = AppUser::where('email', $socialUser->getEmail())->first();
                        if ($user) {
                            $user->update([
                                'social_id' => $socialUser->getId(),
                                'social_provider' => $request->provider,
                                'avatar_url' => $user->avatar_url ?? $socialUser->getAvatar(),
                            ]);
                        }
                    }

                    // B. ROBUST GUEST FINDER (Fixing the issue)
                    $guestUserOnDevice = null;
                    $device = Device::where('device_uuid', $request->device_uuid)->first();

                    // Manually check using app_user_id to avoid Relationship bugs
                    if ($device && $device->app_user_id) {
                        $linkedUser = AppUser::find($device->app_user_id);
                        if ($linkedUser && $linkedUser->is_guest) {
                            $guestUserOnDevice = $linkedUser;
                        }
                    }

                    if ($user) {
                        // === SCENARIO 1: ACCOUNT EXISTS ===
                        // Prevent double-dipping.
                        if ($guestUserOnDevice && $guestUserOnDevice->id !== $user->id) {
                            $guestUserOnDevice->update(['credits' => 0]);
                        }
                    } else {
                        // === SCENARIO 2: NEW REGISTRATION (The Fix) ===

                        if ($guestUserOnDevice) {
                            // [CONVERT]: We found the guest manually, now update them!
                            $guestUserOnDevice->update([
                                'name' => $socialUser->getName(),
                                'email' => $socialUser->getEmail(),
                                'social_id' => $socialUser->getId(),
                                'social_provider' => $request->provider,
                                'avatar_url' => $socialUser->getAvatar(),
                                'is_guest' => false,
                            ]);
                            $user = $guestUserOnDevice;
                        } else {
                            // [CREATE]: Truly new user
                            $user = AppUser::create([
                                'name' => $socialUser->getName(),
                                'email' => $socialUser->getEmail(),
                                'social_id' => $socialUser->getId(),
                                'social_provider' => $request->provider,
                                'avatar_url' => $socialUser->getAvatar(),
                                'is_guest' => false,
                                'credits' => 3
                            ]);
                        }
                    }

                } catch (\Exception $e) {
                    return response()->json(['message' => 'Social login failed.', 'error' => $e->getMessage()], 401);
                }
            }

            // ====================================================
            // 2. HANDLE GUEST LOGIN
            // ====================================================
            if (!$user) {
                $device = Device::where('device_uuid', $request->device_uuid)->first();

                // Manual check again
                if ($device && $device->app_user_id) {
                    $linkedUser = AppUser::find($device->app_user_id);
                    if ($linkedUser && $linkedUser->is_guest) {
                        $user = $linkedUser;
                    }
                }

                if (!$user) {
                    $user = AppUser::create([
                        'name' => 'Guest-' . substr($request->device_uuid, 0, 6),
                        'is_guest' => true,
                        'credits' => 3
                    ]);
                }
            }

            // ====================================================
            // 3. FINALIZE
            // ====================================================
            Device::updateOrCreate(
                ['device_uuid' => $request->device_uuid],
                [
                    'app_user_id' => $user->id, // Ensure this column name matches your DB
                    'fcm_token' => $request->fcm_token,
                    'platform' => $request->platform,
                    'language' => $request->language ?? 'en',
                    'app_version' => $request->app_version,
                ]
            );

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
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

                    // A. Check for Existing Social Account
                    $user = AppUser::where('social_id', $socialUser->getId())
                        ->where('social_provider', $request->provider)
                        ->first();

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

                    // B. Check Device for existing Guest
                    $device = Device::where('device_uuid', $request->device_uuid)->first();
                    $guestUserOnDevice = null;
                    if ($device && $device->app_user_id) {
                        $linkedUser = AppUser::find($device->app_user_id);
                        if ($linkedUser && $linkedUser->is_guest) {
                            $guestUserOnDevice = $linkedUser;
                        }
                    }

                    if ($user) {
                        // === SCENARIO 1: EXISTING SOCIAL USER ===
                        // If this device was holding a Guest, WIPE their credits to prevent double-dipping
                        if ($guestUserOnDevice && $guestUserOnDevice->id !== $user->id) {
                            $guestUserOnDevice->update(['credits' => 0]);
                        }
                    } else {
                        // === SCENARIO 2: NEW SOCIAL REGISTRATION ===
                        if ($guestUserOnDevice) {
                            // CONVERT Guest -> Social (Keep Credits)
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
                            // CREATE New User (Welcome Bonus)
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

                // A. Try to Restore Existing Guest
                if ($device && $device->app_user_id) {
                    $linkedUser = AppUser::find($device->app_user_id);
                    if ($linkedUser && $linkedUser->is_guest) {
                        $user = $linkedUser;
                    }
                }

                // B. Create New Guest (If restore failed)
                if (!$user) {
                    // [THE FIX]: If device exists, it was used by a Social account. No free credits.
                    $credits = $device ? 0 : 3;

                    $user = AppUser::create([
                        'name' => 'Guest-' . substr($request->device_uuid, 0, 6),
                        'is_guest' => true,
                        'credits' => $credits // 0 if device known, 3 if new
                    ]);
                }
            }

            // ====================================================
            // 3. FINALIZE (Link Device)
            // ====================================================
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
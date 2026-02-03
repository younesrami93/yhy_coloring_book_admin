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
    // 1. Validation
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
        // A. HANDLE SOCIAL LOGIN (Google/Facebook)
        // ====================================================
        if ($request->has('provider')) {
            try {
                // 1. Verify Token with Social Provider
                $socialUser = Socialite::driver($request->provider)
                    ->stateless()
                    ->userFromToken($request->social_token);

                $socialId = $socialUser->getId();
                $email = $socialUser->getEmail();
                $avatar = $socialUser->getAvatar();
                $name = $socialUser->getName();

                // 2. Check if Social Account ALREADY EXISTS
                $user = AppUser::where('social_id', $socialId)
                    ->where('social_provider', $request->provider)
                    ->first();

                // Fallback: Link by email to prevent duplicates if user signed up differently
                if (!$user && $email) {
                    $user = AppUser::where('email', $email)->first();
                    if ($user) {
                        $user->update([
                            'social_id' => $socialId,
                            'social_provider' => $request->provider,
                            'avatar_url' => $user->avatar_url ?? $avatar,
                        ]);
                    }
                }

                // 3. Find any GUEST currently on this device
                // We need this to decide whether to Convert or Clear Credits.
                $device = Device::where('device_uuid', $request->device_uuid)->with('user')->first();
                $guestUserOnDevice = ($device && $device->user && $device->user->is_guest) ? $device->user : null;

                if ($user) {
                    // ---------------------------------------------------------
                    // CASE 1: EXISTING ACCOUNT FOUND
                    // Logic: The user already has an account (so they used their free trial).
                    // Action: If there is a Guest account here, clear its credits to prevent farming.
                    // ---------------------------------------------------------
                    
                    if ($guestUserOnDevice && $guestUserOnDevice->id !== $user->id) {
                         // Wipe the guest credits so they don't get 3 + 3
                         $guestUserOnDevice->update(['credits' => 0]); 
                    }
                    
                    // Proceed with logging in $user (credits unchanged)

                } else {
                    // ---------------------------------------------------------
                    // CASE 2: NEW REGISTRATION
                    // Logic: This social account is new. We need to create a user.
                    // Action: If a Guest exists, CONVERT them (keep credits). If not, Create New.
                    // ---------------------------------------------------------

                    if ($guestUserOnDevice) {
                        // [CONVERT]: Update existing Guest row to become this Social User
                        $guestUserOnDevice->update([
                            'name' => $name,
                            'email' => $email,
                            'social_id' => $socialId,
                            'social_provider' => $request->provider,
                            'avatar_url' => $avatar,
                            'is_guest' => false, // Upgrade status
                        ]);
                        $user = $guestUserOnDevice; // Log them in as the converted user
                    } else {
                        // [CREATE]: No guest history found. Fresh start.
                        $user = AppUser::create([
                            'name' => $name,
                            'email' => $email,
                            'social_id' => $socialId,
                            'social_provider' => $request->provider,
                            'avatar_url' => $avatar,
                            'is_guest' => false,
                            'credits' => 3 // Standard Welcome Bonus
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

        // ====================================================
        // B. HANDLE GUEST LOGIN (No Provider)
        // ====================================================
        if (!$user) {
             // 1. Try to find existing guest by Device UUID
             $device = Device::where('device_uuid', $request->device_uuid)->with('user')->first();

             if ($device && $device->user && $device->user->is_guest) {
                 // RESTORE: Log back into existing guest account
                 $user = $device->user;
             } else {
                 // CREATE: Brand new guest
                 $user = AppUser::create([
                     'name' => 'Guest-' . substr($request->device_uuid, 0, 6),
                     'is_guest' => true,
                     'credits' => 3
                 ]);
             }
        }

        
        // ====================================================
        // C. FINALIZATION
        // ====================================================
        
        // Link this device to the final User ID (Critical for persistence)
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

        // Generate API Token
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
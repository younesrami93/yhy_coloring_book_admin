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
                    // A. Verify Token with Social Provider
                    $socialUser = Socialite::driver($request->provider)
                        ->stateless()
                        ->userFromToken($request->social_token);

                    $socialId = $socialUser->getId();
                    $email = $socialUser->getEmail();
                    $avatar = $socialUser->getAvatar();
                    $name = $socialUser->getName();

                    // B. Check if this Social Account ALREADY EXISTS
                    $user = AppUser::where('social_id', $socialId)
                        ->where('social_provider', $request->provider)
                        ->first();

                    // Fallback: Link by email if exists
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

                    // C. Look for an existing GUEST on this device
                    $device = Device::where('device_uuid', $request->device_uuid)->with('user')->first();
                    $guestUserOnDevice = ($device && $device->user && $device->user->is_guest) ? $device->user : null;

                    if ($user) {
                        // ---------------------------------------------------------
                        // SCENARIO: EXISTING ACCOUNT (Not Guest)
                        // Rule: "Check for guest accounts and clear their credit"
                        // ---------------------------------------------------------

                        if ($guestUserOnDevice && $guestUserOnDevice->id !== $user->id) {
                            // 1. Clear the Guest's credits (Prevent double dipping)
                            $guestUserOnDevice->update(['credits' => 0]);

                            // 2. Optional: We usually delete the guest or unlink the device
                            // so they don't accidentally log back into it later.
                            // But strictly following your rule: just clear credit.
                        }

                        // User is logged in, no credits added.

                    } else {
                        // ---------------------------------------------------------
                        // SCENARIO: NEW SOCIAL LOGIN
                        // Rule: "Found a guest login... dont create new user, just edit (convert)"
                        // ---------------------------------------------------------

                        if ($guestUserOnDevice) {
                            // CASE A: CONVERT GUEST -> SOCIAL
                            // We keep the ID, Credits, and Generations. We just change the identity.
                            $guestUserOnDevice->update([
                                'name' => $name,
                                'email' => $email,
                                'social_id' => $socialId,
                                'social_provider' => $request->provider,
                                'avatar_url' => $avatar,
                                'is_guest' => false, // No longer a guest
                            ]);
                            $user = $guestUserOnDevice;
                        } else {
                            // CASE B: BRAND NEW USER (No Guest found)
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
            // 2. HANDLE GUEST LOGIN (No Provider)
            // ====================================================
            if (!$user) {
                // Rule: "Check if already a guest with the same device uid... login as it"

                $device = Device::where('device_uuid', $request->device_uuid)->with('user')->first();

                if ($device && $device->user && $device->user->is_guest) {
                    // RESTORE EXISTING GUEST
                    $user = $device->user;
                } else {
                    // Rule: "No guest account related to him... continue normal and give 3 credits"
                    $user = AppUser::create([
                        'name' => 'Guest-' . substr($request->device_uuid, 0, 6),
                        'is_guest' => true,
                        'credits' => 3
                    ]);
                }
            }

            // ====================================================
            // 3. FINALIZATION
            // ====================================================

            // Link this device to the final User ID
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
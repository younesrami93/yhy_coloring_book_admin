<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RevenueCatWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. SECURITY: Verify the request comes from RevenueCat
        // You will set this secret header in the RevenueCat Dashboard later.
        $secret = $request->header('X-RevenueCat-Secret');
        if ($secret !== env('REVENUECAT_WEBHOOK_SECRET')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->all();
        $event = $data['event'] ?? null;

        if (!$event) {
            return response()->json(['message' => 'Invalid event data'], 400);
        }

        // 2. FILTER: We only care about successful purchases
        // NON_RENEWING_PURCHASE is usually used for consumables (tokens)
        // INITIAL_PURCHASE might be used depending on your setup
        $validTypes = ['NON_RENEWING_PURCHASE', 'INITIAL_PURCHASE', 'TEST'];
        if (!in_array($event['type'], $validTypes)) {
            // Return 200 so RevenueCat stops retrying; we just don't care about other events (like expiration)
            return response()->json(['message' => 'Event ignored']);
        }


        // HANDLE TEST SPECIFICALLY
        if ($event['type'] === 'TEST') {
            Log::info("RevenueCat Webhook Test Received!");
            return response()->json(['status' => 'success']);
        }


        // 3. IDENTIFY USER: "app_user_id" from RevenueCat MUST match your Laravel AppUser ID
        $appUserId = $event['app_user_id'];
        $user = AppUser::find($appUserId);

        if (!$user) {
            Log::error("RevenueCat Webhook: User not found for ID {$appUserId}");
            return response()->json(['message' => 'User not found'], 404);
        }

        // 4. DETERMINE CREDITS: Map Product IDs to Token Amounts
        $productId = $event['product_id'];
        $creditsToAdd = $this->getCreditsForProduct($productId);

        if ($creditsToAdd > 0) {
            // 5. UPDATE: Increment the user's credits
            // specific to your 'credits' column in AppUser model
            $user->increment('credits', $creditsToAdd);
            Log::info("Added {$creditsToAdd} credits to User {$user->id}. New Balance: {$user->credits}");
        }

        return response()->json(['status' => 'success']);
    }

    private function getCreditsForProduct($productId)
    {
        // Match these strings EXACTLY to the Product IDs in Google Play / App Store
        return match ($productId) {
            'tokens_10' => 10,
            'tokens_50' => 50,
            'tokens_100' => 100,
            'tokens_500' => 500,
            default => 0,
        };
    }
}
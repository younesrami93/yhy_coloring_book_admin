<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessGeneration;
use App\Models\Generation;
use App\Models\Style;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class GenerationApiController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        // 1. Check Credits
        // Guests start with 5, Registered might have more. Logic is identical.
        if ($user->credits < 1) {
            return response()->json([
                'message' => 'Insufficient credits. Please purchase a package or watch an ad.'
            ], 402); // 402 Payment Required
        }

        // 2. Validate Input
        $request->validate([
            'style_id' => 'required|exists:styles,id',
            'image' => 'required|image|max:10240', // Max 10MB
            'prompt' => 'nullable|string|max:500', // Optional user custom prompt
        ]);

        $style = Style::find($request->style_id);

        // 3. Upload & Optimize Image
        // We save the "Original" so the AI can read it.
        $path = $request->file('image')->store('generations/originals', 'public');
        $fullPath = asset('storage/' . $path);

        // 4. Create Database Record
        $generation = Generation::create([
            'user_id' => $user->id,
            'style_name' => $style->title,
            // We combine the Style's strict prompt with any user addition
            'prompt_used' => $style->prompt . ($request->prompt ? ' ' . $request->prompt : ''),
            'original_image_url' => $fullPath,
            'status' => 'pending', // Waiting for the Worker
            'cost_in_credits' => 1,
        ]);

        // 5. Deduct Credit
        $user->decrement('credits', 1);

        $tier = 'standard';
        ProcessGeneration::dispatch($generation, $tier);

        // 6. Update Style Stats (for dashboard popularity)
        $style->increment('usage_count');

        return response()->json([
            'message' => 'Generation started.',
            'generation_id' => $generation->id,
            'remaining_credits' => $user->credits,
            'status' => 'pending'
        ], 201);
    }

    /**
     * Check status of a specific generation (Polling)
     */
    public function show($id)
    {
        $generation = Generation::where('id', $id)
            ->where('user_id', request()->user()->id)
            ->firstOrFail();

        return response()->json([
            'id' => $generation->id,
            'status' => $generation->status,
            'result_url' => $generation->processed_image_url,
            'created_at' => $generation->created_at,
        ]);
    }
}
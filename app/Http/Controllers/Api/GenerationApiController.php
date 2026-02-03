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
        // We count how many generations are currently running (reserved credits)
        $runningCount = Generation::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        $effectiveBalance = $user->credits - $runningCount;

        if ($effectiveBalance < 1) {
            return response()->json([
                'message' => "Insufficient credits. You have {$user->credits} credits but {$runningCount} generation(s) are already in progress."
            ], 402);
        }

        // 2. Validate Input
        $request->validate([
            'style_id' => 'required|exists:styles,id',
            'image' => 'required|image|max:10240', // Max 10MB
            'prompt' => 'nullable|string|max:500',
        ]);

        $style = Style::find($request->style_id);

        $imageFile = $request->file('image');
        $baseFilename = uniqid();


        $image = Image::read($imageFile);
        $image->scaleDown(width: 2048, height: 2048);
        $encodedOriginal = $image->toJpeg(quality: 90);

        $origPath = 'generations/originals/src/' . $baseFilename . '.jpg';
        Storage::disk('r2')->put($origPath, (string) $encodedOriginal, 'public');

        // B. Create MD Thumbnail (WebP - 500px)
        // We reuse the $image object, but scale it down further
        $encodedMd = $image->scaleDown(width: 500)->toWebp(quality: 80);
        $mdPath = 'generations/originals/thumbs-md/' . $baseFilename . '_md.webp';
        Storage::disk('r2')->put($mdPath, (string) $encodedMd, 'public');

        // C. Create SM Thumbnail (WebP - 200px)
        $encodedSm = $image->scaleDown(width: 200)->toWebp(quality: 80);
        $smPath = 'generations/originals/thumbs-sm/' . $baseFilename . '_sm.webp';
        Storage::disk('r2')->put($smPath, (string) $encodedSm, 'public');

        // 4. Create Database Record
        $generation = Generation::create([
            'user_id' => $user->id,
            'style_name' => $style->title,
            'prompt_used' => $style->prompt . ($request->prompt ? ' ' . $request->prompt : ''),
            // Save all URLs
            'original_image_url' => $origPath,
            'original_thumb_md' => $mdPath,
            'original_thumb_sm' => $smPath,

            'status' => 'pending',
            'cost_in_credits' => 1,
        ]);

        $tier = 'standard';
        ProcessGeneration::dispatch($generation, $tier);

        $style->increment('usage_count');

        $user->decrement('credits', $generation->cost_in_credits);

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

    public function show(Request $request, $id)
    {
        $user = $request->user();

        // 1. Eager load 'style' to get the name (assuming you have a 'style()' relationship)
        $generation = Generation::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$generation) {
            return response()->json([
                'message' => 'Generation not found.'
            ], 404);
        }

        // 2. Map data strictly to match Flutter's Generation.fromJson
        

        // 3. Return wrapped in 'data' key for consistency with pagination
        return response()->json(['data' => $generation]);
    }

    public function index(Request $request)
    {
        $generations = Generation::where('user_id', $request->user()->id)
            ->latest() // Equivalent to orderBy('created_at', 'desc')
            ->paginate(perPage: 10);

        return response()->json($generations);
    }
}
<?php

namespace App\Jobs;

use App\Models\Generation;
use App\Services\NanoBananaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Laravel\Facades\Image;
use Log;

class ProcessGeneration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60; // 1 minute   
    public $generation;
    public $tier; // 'standard' or 'pro'

    /**
     * Create a new job instance.
     */
    public function __construct(Generation $generation, $tier = 'standard')
    {
        $this->generation = $generation;
        $this->tier = $tier;
    }

    /**
     * Execute the job.
     */
    public function handle(NanoBananaService $aiService): void
    {
        // 1. Update Status to Processing
        $this->generation->update(['status' => 'processing']);

        // 2. Call AI Service
        $resultUrl = $aiService->generate(
            $this->generation->original_image_url,
            $this->generation->prompt_used,
            $this->tier
        );

        if (!$resultUrl) {
            $this->generation->update(['status' => 'failed']);
            $this->generation->user->increment('credits', $this->generation->cost_in_credits);
            return;
        }


        $contents = Http::withoutVerifying()->get($resultUrl)->body();
        $baseName = Str::random(40);

        $image = Image::read($contents);
        $encodedMain = $image->toWebp(quality: 80);
        // A. Save Original Result
        $mainPath = 'generations/results/src/' . $baseName . '.webp';
        Storage::disk('r2')->put($mainPath, (string) $encodedMain, 'public');
        // B. Create MD Thumbnail (500px)

        
        $encodedMd = $image->scaleDown(width: 500)->toWebp(quality: 80);
        $mdPath = 'generations/results/thumbs/' . $baseName . '_md.webp';
        Storage::disk('r2')->put($mdPath, (string) $encodedMd, 'public');

        // C. Create SM Thumbnail (WebP - 200px)
        $encodedSm = $image->scaleDown(width: 200)->toWebp(quality: 80);
        $smPath = 'generations/results/thumbs/' . $baseName . '_sm.webp';
        Storage::disk('r2')->put($smPath, (string) $encodedSm, 'public');


        // 4. Mark Complete & Update All URLs
        $this->generation->update([
            'status' => 'completed',
            'processed_image_url' => $mainPath,
            'processed_thumb_md' => $mdPath,
            'processed_thumb_sm' => $smPath,
        ]);

        // 5. Deduct Credit
    }


    public function failed(\Throwable $exception): void
    {
        // 1. Mark generation as failed so it stops counting as "Reserved Credit"
        $this->generation->update([
            'status' => 'failed',
        ]);
        
        $this->generation->user->increment('credits', $this->generation->cost_in_credits);

        Log::error("Generation Job Failed: " . $exception->getMessage());
    }

}
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

class ProcessGeneration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
            // Optional: Refund credits here if failed
            $this->generation->user->increment('credits', $this->generation->cost_in_credits);
            return;
        }

        // 3. Download & Save Result Locally (Don't rely on external URL forever)
        //$contents = file_get_contents($resultUrl);
        $contents = Http::withoutVerifying()->get($resultUrl)->body();
        $filename = 'generations/results/' . Str::random(40) . '.png';
        Storage::disk('public')->put($filename, $contents);

        // 4. Mark Complete
        $this->generation->update([
            'status' => 'completed',
            'processed_image_url' => asset('storage/' . $filename)
        ]);
    }
}
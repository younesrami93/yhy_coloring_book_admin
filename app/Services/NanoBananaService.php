<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NanoBananaService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.nanobanana.key');
        $this->baseUrl = 'https://api.nanobananaapi.ai'; // Hardcoded per docs
    }

    public function generate($localImageUrl, $prompt, $tier = 'standard')
    {
        // 1. Resolve Image URL
        // PROBLEM: The AI cannot access 'http://127.0.0.1/...'.
        // SOLUTION: If on localhost, send a PUBLIC placeholder image for testing.
        $publicUrl = $localImageUrl;
        
        if (str_contains($localImageUrl, '127.0.0.1') || str_contains($localImageUrl, 'localhost')) {
            Log::warning("Nano Banana: Localhost detected. Switching to TEST image because AI cannot access local files.");
            // A reliable public image for testing
            $publicUrl = "https://trueapps.website/photo.jpg";
        }

        try {
            // 2. Submit Task (POST)
            $response = Http::withToken($this->apiKey)
                ->withoutVerifying()
                ->post("{$this->baseUrl}/api/v1/nanobanana/generate", [
                    'prompt' => "coloring book page, " . $prompt,
                    'type' => "IMAGETOIAMGE", // YES, this typo is required by their docs!
                    'imageUrls' => [$publicUrl], // Must be array
                    'numImages' => 1,
                    'image_size' => "1:1",
                    'callBackUrl' => "https://google.com" // Required dummy field
                ]);

            if ($response->failed()) {
                Log::error('Nano Banana Submit Error: ' . $response->body());
                return null;
            }

            $taskId = $response->json()['data']['taskId'] ?? null;

            if (!$taskId) {
                Log::error('Nano Banana: No Task ID. Full Response: ' . $response->body());
                return null;
            }

            // 3. Poll for Results (GET)
            // We loop here for up to 60 seconds waiting for the image.
            return $this->pollForCompletion($taskId);

        } catch (\Exception $e) {
            Log::error('AI Service Exception: ' . $e->getMessage());
            return null;
        }
    }

    private function pollForCompletion($taskId)
    {
        $attempts = 0;
        $maxAttempts = 20; // 20 * 3 seconds = 60 seconds max wait

        while ($attempts < $maxAttempts) {
            sleep(3); // Wait 3 seconds
            
            $response = Http::withToken($this->apiKey)
                ->withoutVerifying()
                ->get("{$this->baseUrl}/api/v1/nanobanana/record-info", [
                    'taskId' => $taskId
                ]);
            
            $data = $response->json()['data'] ?? [];
            $status = $data['successFlag'] ?? 0; // 0=Generating, 1=Success, 3=Failed

            if ($status === 1) {
                // Success! Return the result URL
                return $data['response']['resultImageUrl'] ?? null;
            }
            
            if ($status === 3 || $status === 2) {
                Log::error('Nano Banana Task Failed: ' . ($data['errorMessage'] ?? 'Unknown error'));
                return null;
            }

            $attempts++;
        }

        Log::error('Nano Banana: Timeout waiting for generation.');
        return null;
    }
}
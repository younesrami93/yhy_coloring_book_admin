<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Style;
use Illuminate\Http\Request;

class StyleApiController extends Controller
{
    public function index()
    {
        // Return only active styles for the app list
        $styles = Style::where('is_active', true)
            ->orderBy('usage_count', 'desc')
            ->select(['id', 'title', 'thumbnail_url', 'example_before_url', 'example_after_url', 'prompt'])
            ->get();

        return response()->json(['data' => $styles]);
    }
}
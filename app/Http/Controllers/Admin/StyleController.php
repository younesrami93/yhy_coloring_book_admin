<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Style;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class StyleController extends Controller
{
    public function index()
    {
        // Latest styles first
        $styles = Style::latest()->get();
        return view('admin.styles.index', compact('styles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'prompt' => 'required|string',
            'thumbnail' => 'required|image|max:5120', // 5MB max
            'example_before' => 'required|image|max:5120',
            'example_after' => 'required|image|max:5120',
        ]);

        // Process Images
        $data['thumbnail_url'] = $this->uploadAndResize($request->file('thumbnail'), 300);
        $data['example_before_url'] = $this->uploadAndResize($request->file('example_before'), 800);
        $data['example_after_url'] = $this->uploadAndResize($request->file('example_after'), 800);

        Style::create($data);

        return back()->with('success', 'Style created successfully.');
    }

    public function update(Request $request, Style $style)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'prompt' => 'required|string',
            'thumbnail' => 'nullable|image|max:5120',
            'example_before' => 'nullable|image|max:5120',
            'example_after' => 'nullable|image|max:5120',
        ]);

        // Only upload if new files provided
        if ($request->hasFile('thumbnail'))
            $data['thumbnail_url'] = $this->uploadAndResize($request->file('thumbnail'), 300);

        if ($request->hasFile('example_before'))
            $data['example_before_url'] = $this->uploadAndResize($request->file('example_before'), 800);

        if ($request->hasFile('example_after'))
            $data['example_after_url'] = $this->uploadAndResize($request->file('example_after'), 800);

        $style->update($data);

        return back()->with('success', 'Style updated successfully.');
    }

    public function destroy(Style $style)
    {
        $style->delete();
        return back()->with('success', 'Style deleted (archived).');
    }

    // Helper to Resize and Save
    private function uploadAndResize($file, $size)
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = storage_path('app/public/styles/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($path)))
            mkdir(dirname($path), 0777, true);

        // Resize (Scale Down keeps aspect ratio)
        Image::read($file)->scaleDown(width: $size)->save($path);

        return asset('storage/styles/' . $filename);
    }
}
@extends('layouts.app')
@section('title', 'Add Style')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-slate-200">
    <h2 class="text-xl font-bold mb-6">Create New Style</h2>
    
    <form action="{{ route('styles.store') }}" method="POST">
        @csrf
        
        <div class="mb-5">
            <label class="block text-sm font-medium text-slate-700 mb-1">Style Title</label>
            <input type="text" name="title" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="e.g. Mandala Art">
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-slate-700 mb-1">AI Prompt</label>
            <textarea name="prompt" rows="3" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Detailed instruction for AI..."></textarea>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Thumbnail URL</label>
                <input type="url" name="thumbnail_url" class="w-full p-2 text-sm border rounded">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Before Ex. URL</label>
                <input type="url" name="example_before_url" class="w-full p-2 text-sm border rounded">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">After Ex. URL</label>
                <input type="url" name="example_after_url" class="w-full p-2 text-sm border rounded">
            </div>
        </div>

        <button type="submit" class="bg-slate-900 text-white px-6 py-2 rounded-lg hover:bg-slate-800 transition">Save Style</button>
    </form>
</div>
@endsection
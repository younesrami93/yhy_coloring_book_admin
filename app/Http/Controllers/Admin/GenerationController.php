<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Generation;
use Illuminate\Http\Request;

class GenerationController extends Controller
{
    public function index()
    {
        $generations = Generation::with('user') // Eager load user to avoid N+1 query
            ->latest()
            ->paginate(20);

        return view('admin.generations.index', compact('generations'));
    }
}
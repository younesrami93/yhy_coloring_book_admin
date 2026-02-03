<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use Illuminate\Http\Request;

class AppUserController extends Controller
{
    public function index(Request $request)
    {
        $query = AppUser::query()->withCount('devices');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Order by latest created
        $users = $query->latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function addCredits(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'type' => 'required|in:add,remove' // simple logic to add or remove
        ]);

        $user = AppUser::findOrFail($id);
        $amount = (int) $request->amount;

        if ($request->type === 'add') {
            $user->increment('credits', $amount);
            $message = "Added {$amount} credits to {$user->name}.";
        } else {
            // Prevent negative balance
            if ($user->credits < $amount) {
                return back()->with('error', 'User does not have enough credits to remove.');
            }
            $user->decrement('credits', $amount);
            $message = "Removed {$amount} credits from {$user->name}.";
        }

        return back()->with('success', $message);
    }

    public function destroy($id)
    {
        $user = AppUser::findOrFail($id);

        // Soft delete acts as a "Ban" essentially
        $user->delete();

        return back()->with('success', 'User has been banned (soft deleted).');
    }
}
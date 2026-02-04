<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\Generation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Determine Date Range
        $range = $request->input('range', '30d');
        $customStart = $request->input('start');
        $customEnd = $request->input('end');

        $now = Carbon::now();
        $startDate = $now->copy()->subDays(30);
        $endDate = $now;
        $label = 'Last 30 Days';

        switch ($range) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $label = 'Today';
                break;
            case 'yesterday':
                $startDate = $now->copy()->subDay()->startOfDay();
                $endDate = $now->copy()->subDay()->endOfDay();
                $label = 'Yesterday';
                break;
            case '7d':
                $startDate = $now->copy()->subDays(7)->startOfDay();
                $label = 'Last 7 Days';
                break;
            case '30d':
                $startDate = $now->copy()->subDays(30)->startOfDay();
                $label = 'Last 30 Days';
                break;
            case 'month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $label = 'This Month';
                break;
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                $label = 'Last Month';
                break;
            case '3m':
                $startDate = $now->copy()->subMonths(3)->startOfDay();
                $label = 'Last 3 Months';
                break;
            case 'custom':
                if ($customStart && $customEnd) {
                    $startDate = Carbon::parse($customStart)->startOfDay();
                    $endDate = Carbon::parse($customEnd)->endOfDay();
                    $label = 'Custom Range';
                }
                break;
        }

        // 2. Fetch Key Metrics (In Range)
        $totalGenerations = Generation::whereBetween('created_at', [$startDate, $endDate])->count();
        $creditsConsumed = Generation::whereBetween('created_at', [$startDate, $endDate])->sum('cost_in_credits');

        // User Stats (In Range)
        $newUsers = AppUser::whereBetween('created_at', [$startDate, $endDate])->count();
        $newGuests = AppUser::whereBetween('created_at', [$startDate, $endDate])->where('is_guest', true)->count();
        $newSocial = AppUser::whereBetween('created_at', [$startDate, $endDate])->where('is_guest', false)->count();

        // User Stats (Lifetime)
        $totalUsersLifetime = AppUser::count();

        // Calculate "Success Rate"
        $totalAttempts = Generation::whereBetween('created_at', [$startDate, $endDate])->count();
        $successful = Generation::whereBetween('created_at', [$startDate, $endDate])->where('status', 'completed')->count();
        $successRate = $totalAttempts > 0 ? round(($successful / $totalAttempts) * 100) : 100;

        // 3. Prepare Chart Data
        $chartData = Generation::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $chartLabels = [];
        $chartValues = [];
        $period = new \DatePeriod($startDate->copy()->startOfDay(), new \DateInterval('P1D'), $endDate->copy()->endOfDay());

        foreach ($period as $date) {
            $formatted = $date->format('Y-m-d');
            $chartLabels[] = $date->format('M d');
            $record = $chartData->firstWhere('date', $formatted);
            $chartValues[] = $record ? $record->count : 0;
        }

        // 4. Recent Activity
        $recentGenerations = Generation::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalGenerations', 'creditsConsumed', 'newUsers', 'newGuests', 'newSocial',
            'totalUsersLifetime', 'successRate', 'recentGenerations',
            'chartLabels', 'chartValues', 'range', 'label', 'startDate', 'endDate'
        ));
    }
}
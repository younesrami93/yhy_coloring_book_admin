@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Overview</h1>
            <p class="text-slate-500 text-sm">
                Showing data for: <span class="font-semibold text-blue-600">{{ $label }}</span>
                <span class="text-xs text-slate-400 ml-1">({{ $startDate->format('M d') }} - {{ $endDate->format('M d') }})</span>
            </p>
        </div>

        <div class="relative group z-20">
            <button class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-lg shadow-sm hover:bg-slate-50 transition">
                <i class="far fa-calendar-alt text-slate-400"></i>
                <span class="text-sm font-medium">{{ $label }}</span>
                <i class="fas fa-chevron-down text-xs ml-2 text-slate-400"></i>
            </button>

            <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 hidden group-hover:block animate-fade-in-up">
                <div class="py-1">
                    <a href="?range=today" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 {{ $range == 'today' ? 'bg-blue-50 text-blue-600' : '' }}">Today</a>
                    <a href="?range=yesterday" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 {{ $range == 'yesterday' ? 'bg-blue-50 text-blue-600' : '' }}">Yesterday</a>
                    <a href="?range=7d" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 {{ $range == '7d' ? 'bg-blue-50 text-blue-600' : '' }}">Last 7 Days</a>
                    <a href="?range=30d" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 {{ $range == '30d' ? 'bg-blue-50 text-blue-600' : '' }}">Last 30 Days</a>
                    <a href="?range=month" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 {{ $range == 'month' ? 'bg-blue-50 text-blue-600' : '' }}">This Month</a>
                    <a href="?range=3m" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 {{ $range == '3m' ? 'bg-blue-50 text-blue-600' : '' }}">Last 3 Months</a>
                    <div class="border-t border-slate-100 my-1"></div>
                    <button onclick="toggleCustomDate()" class="w-full text-left block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600">Custom Range...</button>
                </div>
            </div>
        </div>
    </div>

    <div id="custom-date-box" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hidden">
        <form action="{{ route('dashboard') }}" method="GET" class="flex items-end gap-4">
            <input type="hidden" name="range" value="custom">
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Start Date</label>
                <input type="date" name="start" required class="block w-full mt-1 border border-slate-200 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">End Date</label>
                <input type="date" name="end" required class="block w-full mt-1 border border-slate-200 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Apply</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 relative overflow-hidden">
            <div class="flex justify-between items-start z-10 relative">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">Total Generations</p>
                    <h3 class="text-3xl font-bold text-slate-800">{{ number_format($totalGenerations) }}</h3>
                </div>
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                    <i class="fas fa-magic"></i>
                </div>
            </div>
            <div class="absolute -bottom-4 -right-4 text-blue-50 opacity-50 transform rotate-12">
                <i class="fas fa-magic text-8xl"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 relative overflow-hidden">
            <div class="flex justify-between items-start z-10 relative">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">Credits Spent</p>
                    <h3 class="text-3xl font-bold text-slate-800">{{ number_format($creditsConsumed) }}</h3>
                </div>
                <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <div class="absolute -bottom-4 -right-4 text-amber-50 opacity-50 transform rotate-12">
                <i class="fas fa-coins text-8xl"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 relative overflow-hidden">
            <div class="flex justify-between items-start z-10 relative">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">New Users</p>
                    <h3 class="text-3xl font-bold text-slate-800">{{ number_format($newUsers) }}</h3>
                </div>
                <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
            <div class="absolute -bottom-4 -right-4 text-purple-50 opacity-50 transform rotate-12">
                <i class="fas fa-users text-8xl"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 relative overflow-hidden">
            <div class="flex justify-between items-start z-10 relative">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">Success Rate</p>
                    <h3 class="text-3xl font-bold text-slate-800">{{ $successRate }}<span class="text-lg text-slate-400">%</span></h3>
                </div>
                <div class="p-2 {{ $successRate > 90 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} rounded-lg">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-slate-800">Generations Over Time</h3>
                </div>
            <div class="relative h-72 w-full">
                <canvas id="generationsChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 flex flex-col">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Recent Activity</h3>
                <a href="{{ route('generations.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">View All</a>
            </div>
            <div class="flex-1 overflow-y-auto max-h-[400px]">
                @if($recentGenerations->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        <i class="far fa-folder-open text-3xl mb-2"></i>
                        <p class="text-sm">No activity in this period.</p>
                    </div>
                @else
                    <ul class="divide-y divide-slate-50">
                        @foreach($recentGenerations as $gen)
                        <li class="p-4 hover:bg-slate-50 transition-colors flex items-center gap-3">
                            <div class="w-10 h-10 rounded bg-slate-100 bg-cover bg-center border border-slate-200"
                                 style="background-image: url('{{ $gen->processed_thumb_sm ?? $gen->original_thumb_sm ?? 'https://placehold.co/100' }}')">
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate">
                                    {{ $gen->style_name ?? 'Unknown Style' }}
                                </p>
                                <p class="text-xs text-slate-500 truncate">
                                    by {{ $gen->user->name ?? 'Guest' }}
                                </p>
                            </div>

                            <div class="text-right">
                                @if($gen->status == 'completed')
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700">DONE</span>
                                @elseif($gen->status == 'failed')
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">FAIL</span>
                                @else
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">PROC</span>
                                @endif
                                <p class="text-[10px] text-slate-400 mt-1">{{ $gen->created_at->diffForHumans(null, true) }}</p>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCustomDate() {
        $('#custom-date-box').slideToggle();
    }

    // Initialize Chart
    const ctx = document.getElementById('generationsChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Generations',
                data: {!! json_encode($chartValues) !!},
                borderColor: '#2563eb', // Blue-600
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2563eb',
                fill: true,
                tension: 0.4 // Smooth curves
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });
</script>
@endsection
@extends('layouts.app')

@section('title', 'App Overview')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        
        <div class="bg-white p-6 rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-sm font-medium">Total Pages Generated</h3>
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                    <i class="fas fa-magic"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-slate-800">12,405</div>
            <p class="text-xs text-green-500 mt-2 flex items-center gap-1">
                <i class="fas fa-arrow-up"></i> +12% this week
            </p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-sm font-medium">Active Users</h3>
                <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-slate-800">842</div>
            <p class="text-xs text-slate-400 mt-2">Last 30 days</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-sm font-medium">Credits Consumed</h3>
                <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-slate-800">58,000</div>
            <p class="text-xs text-slate-400 mt-2">~ $580.00 Value</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-sm font-medium">AI API Status</h3>
                <div class="p-2 bg-green-50 rounded-lg text-green-600">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="text-xl font-bold text-slate-800">Operational</div>
            <p class="text-xs text-slate-400 mt-2">Latency: 1.2s avg</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-semibold text-slate-800">Recent Transformations</h3>
            <button class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-3 font-medium">Original / Result</th>
                        <th class="px-6 py-3 font-medium">User</th>
                        <th class="px-6 py-3 font-medium">Style</th>
                        <th class="px-6 py-3 font-medium">Date</th>
                        <th class="px-6 py-3 font-medium text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-10 h-10 bg-slate-200 rounded bg-cover" style="background-image: url('https://placehold.co/100')"></div>
                                <i class="fas fa-arrow-right text-slate-300 text-xs"></i>
                                <div class="w-10 h-10 bg-slate-200 rounded bg-cover border border-blue-200" style="background-image: url('https://placehold.co/100/black/white?text=Art')"></div>
                            </div>
                        </td>
                        <td class="px-6 py-3 font-medium text-slate-700">user@example.com</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 bg-slate-100 rounded text-xs text-slate-600">Mandala</span></td>
                        <td class="px-6 py-3 text-slate-500">2 mins ago</td>
                        <td class="px-6 py-3 text-right">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Completed</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
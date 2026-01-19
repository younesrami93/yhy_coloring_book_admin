@extends('layouts.app')

@section('title', 'Login - YHY Coloring Book')

@section('content')
    <div class="w-full max-w-[400px] bg-white p-10 rounded-2xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] border border-slate-100">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-lg shadow-blue-500/30 mb-4">
                <i class="fas fa-palette text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">YHY Coloring Book</h1>
            <p class="text-slate-400 text-sm mt-1 font-medium uppercase tracking-wider">Admin Portal</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-3 bg-red-50 border border-red-100 rounded-lg flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                <div class="text-sm text-red-600 font-medium">
                    {{ $errors->first() }}
                </div>
            </div>
        @endif

        <form action="{{ url('admin/login') }}" method="POST">
            @csrf
            
            <div class="mb-5">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-slate-400 text-sm"></i>
                    </div>
                    <input type="email" name="email" required autofocus placeholder="admin@yhy-coloring.com"
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                </div>
            </div>

            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Password</label>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-slate-400 text-sm"></i>
                    </div>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                </div>
            </div>

            <div class="flex items-center justify-between mb-8">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition-colors">
                    <span class="text-sm text-slate-500 group-hover:text-slate-700 transition-colors">Keep me signed in</span>
                </label>
                <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">Forgot password?</a>
            </div>

            <button type="submit" 
                class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-3.5 rounded-xl transition-all shadow-xl shadow-slate-900/10 hover:shadow-slate-900/20 flex items-center justify-center gap-2 group">
                <span>Sign In to Dashboard</span>
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>
    </div>

    <div class="text-center mt-8">
        <p class="text-slate-400 text-xs">
            &copy; {{ date('Y') }} YHY Coloring Book. All rights reserved.
        </p>
    </div>
@endsection
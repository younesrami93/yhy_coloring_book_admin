<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Portal')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: '#0f172a', /* Deep Slate */
                        accent: '#3b82f6',  /* Corporate Blue */
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        /* Smooth scrolling & clean scrollbars */
        html {
            scroll-behavior: smooth;
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .active-nav {
            background-color: rgba(255, 255, 255, 0.1);
            border-right: 3px solid #60a5fa;
            color: white !important;
        }
    </style>
</head>

<body class="bg-[#F8FAFC] text-slate-800 font-sans antialiased">



    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-3"></div>

    <script>
        // JS Function to show toast manually
        function showToast(message, type = 'success') {
            const colors = {
                success: 'bg-slate-800 text-white border-l-4 border-green-500',
                error: 'bg-red-600 text-white border-l-4 border-red-800',
                warning: 'bg-amber-500 text-white border-l-4 border-amber-700'
            };

            const html = `
            <div class="toast-item transform translate-y-10 opacity-0 transition-all duration-300 min-w-[300px] p-4 rounded shadow-lg flex items-center justify-between ${colors[type] || colors.success}">
                <span class="font-medium text-sm">${message}</span>
                <button onclick="$(this).parent().remove()" class="ml-4 text-white hover:text-gray-300"><i class="fas fa-times"></i></button>
            </div>
        `;

            const $el = $(html).appendTo('#toast-container');

            // Animate In
            setTimeout(() => $el.removeClass('translate-y-10 opacity-0'), 10);

            // Auto Dismiss
            setTimeout(() => {
                $el.addClass('opacity-0 translate-y-10');
                setTimeout(() => $el.remove(), 300);
            }, 4000);
        }

    // Check for Backend Session Flashes
    @if(session('success')) showToast("{{ session('success') }}", 'success'); @endif
        @if(session('error'))   showToast("{{ session('error') }}", 'error'); @endif
        @if($errors->any())     showToast("Please check the form for errors.", 'error'); @endif
    </script>

    <div class="flex h-screen {{ Auth::check() ? 'overflow-hidden' : 'items-center justify-center bg-[#F1F5F9]' }}">

        @auth
            <aside class="w-72 bg-primary text-slate-400 flex flex-col transition-all duration-300 hidden md:flex z-20">
                <div class="h-20 flex items-center px-8 border-b border-slate-800">
                    <div class="flex items-center gap-3 text-white">
                        <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center font-bold text-lg">L</div>
                        <span class="text-lg font-semibold tracking-wide">YHY<span
                                class="font-light opacity-70">ADMIN</span></span>
                    </div>
                </div>

                <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">

                    <p class="px-4 text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Analytics</p>

                    <a href="{{ route('dashboard') }}"
                        class="{{ request()->routeIs('dashboard') ? 'active-nav' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-md transition-colors">
                        <i class="fas fa-chart-pie w-5"></i> Overview
                    </a>

                    <p class="px-4 text-xs font-semibold uppercase tracking-wider text-slate-500 mt-6 mb-2">App Management
                    </p>

                    <a href="#"
                        class="{{ request()->routeIs('generations.*') ? 'active-nav' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-md transition-colors">
                        <i class="fas fa-images w-5"></i>
                        <span>Generations</span>
                        <span class="ml-auto bg-blue-600 text-white text-xs py-0.5 px-2 rounded-full">New</span>
                    </a>

                    <a href="{{ route('styles.index') }}"
                        class="{{ request()->routeIs('styles.*') ? 'active-nav' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-md transition-colors">
                        <i class="fas fa-palette w-5"></i> Styles & Prompts
                    </a>

                    <a href="#"
                        class="{{ request()->routeIs('users.*') ? 'active-nav' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-md transition-colors">
                        <i class="fas fa-users w-5"></i> App Users
                    </a>

                    <p class="px-4 text-xs font-semibold uppercase tracking-wider text-slate-500 mt-6 mb-2">System</p>

                    <a href="#"
                        class="{{ request()->routeIs('logs.*') ? 'active-nav' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-md transition-colors">
                        <i class="fas fa-server w-5"></i> AI API Logs
                    </a>
                    <a href="#"
                        class="{{ request()->routeIs('credits.*') ? 'active-nav' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-md transition-colors">
                        <i class="fas fa-coins w-5"></i> Credit Packages
                    </a>

                </nav>

                <div class="p-4 border-t border-slate-800">
                    <div class="flex items-center gap-3 px-2">
                        <img src="https://ui-avatars.com/api/?name=Admin+User&background=334155&color=fff"
                            class="w-9 h-9 rounded-full border border-slate-600">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">Administrator</p>
                            <p class="text-xs text-slate-500 truncate">admin@yhy.com</p>
                        </div>
                    </div>
                </div>
            </aside>
        @endauth

        <div class="{{ Auth::check() ? 'flex-1 flex flex-col h-screen relative' : 'w-full max-w-[420px]' }}">

            @auth
                <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10">
                    <div class="flex items-center gap-4">
                        <button class="md:hidden text-slate-500 hover:text-slate-700">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h2 class="text-xl font-semibold text-slate-800">@yield('title')</h2>
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="relative hidden lg:block">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" placeholder="Search..."
                                class="pl-10 pr-4 py-2 w-64 border border-slate-200 rounded-full text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                        </div>

                        <button class="relative text-slate-500 hover:text-slate-800 transition">
                            <i class="far fa-bell text-xl"></i>
                            <span
                                class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border border-white"></span>
                        </button>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="text-sm font-medium text-slate-500 hover:text-red-600 transition">Sign
                                Out</button>
                        </form>
                    </div>
                </header>
            @endauth

            <main class="{{ Auth::check() ? 'flex-1 overflow-x-hidden overflow-y-auto bg-[#F8FAFC] p-8' : '' }}">
                <div class="{{ Auth::check() ? 'max-w-7xl mx-auto animate-fade-in-up' : '' }}">
                    @yield('content')
                </div>
            </main>

        </div>
    </div>

    @stack('scripts')
</body>

</html>
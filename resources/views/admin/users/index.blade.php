@extends('layouts.app')

@section('title', 'App Users')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">User Management</h1>
            <p class="text-slate-500 text-sm">Manage app users, credits, and account status.</p>
        </div>

        <form method="GET" action="{{ route('users.index') }}" class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search by name or email..." 
                   class="pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-64">
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-medium">User</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium">Credits</th>
                        <th class="px-6 py-4 font-medium">Devices</th>
                        <th class="px-6 py-4 font-medium">Joined</th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $user->name ?? 'Guest User' }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email ?? 'No Email' }}</p>
                                        <div class="flex gap-1 mt-1">
                                            @if($user->social_provider == 'google')
                                                <i class="fab fa-google text-slate-400 text-xs"></i>
                                            @elseif($user->social_provider == 'apple')
                                                <i class="fab fa-apple text-slate-400 text-xs"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_guest)
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">Guest</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Registered</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-coins text-amber-500"></i>
                                    <span class="font-bold text-slate-700">{{ number_format($user->credits) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                <i class="fas fa-mobile-alt mr-1"></i> {{ $user->devices_count }}
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openCreditModal({{ $user->id }}, '{{ $user->name }}', {{ $user->credits }})" 
                                            class="text-slate-400 hover:text-blue-600 transition p-2" title="Manage Credits">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>

                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to ban this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-red-600 transition p-2" title="Ban User">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-users text-4xl text-slate-200 mb-3"></i>
                                    <p>No users found matching your search.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>

    <div id="creditModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeCreditModal()"></div>
        
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl p-6 transform transition-all">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Manage Credits</h3>
            <p class="text-sm text-slate-500 mb-6">Update balance for <span id="modalUserName" class="font-semibold text-slate-800"></span></p>

            <form id="creditForm" action="" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Action</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="add" checked class="peer sr-only">
                            <div class="text-center py-2 px-4 rounded border border-slate-200 peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700 hover:bg-slate-50 transition">
                                <i class="fas fa-plus mr-1"></i> Give
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="remove" class="peer sr-only">
                            <div class="text-center py-2 px-4 rounded border border-slate-200 peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:text-red-700 hover:bg-slate-50 transition">
                                <i class="fas fa-minus mr-1"></i> Remove
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Amount</label>
                    <input type="number" name="amount" min="1" value="10" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeCreditModal()" class="px-4 py-2 text-slate-500 hover:text-slate-700 font-medium">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Update Balance</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openCreditModal(userId, userName, currentCredits) {
            // Set user info
            $('#modalUserName').text(userName);
            
            // Set dynamic route
            let url = "{{ route('users.add_credits', ':id') }}";
            url = url.replace(':id', userId);
            $('#creditForm').attr('action', url);

            // Show modal
            $('#creditModal').removeClass('hidden');
        }

        function closeCreditModal() {
            $('#creditModal').addClass('hidden');
        }
    </script>
    @endpush
@endsection
@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Generations History</h2>
                    </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Images</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($generations as $gen)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    #{{ $gen->id }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold uppercase text-xs">
                                            {{ substr($gen->user->name ?? 'G', 0, 2) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $gen->user->name ?? 'Unknown User' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $gen->user->email ?? 'No Email' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        @if($gen->original_image_url)
                                            <div class="group relative cursor-pointer" onclick="openImageModal('{{ $gen->original_image_url }}')">
                                                <img src="{{ $gen->original_image_url }}" alt="Original" class="h-12 w-12 rounded object-cover border border-gray-200 hover:opacity-75">
                                                <span class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-[10px] text-center">Orig</span>
                                            </div>
                                        @endif

                                        @if($gen->processed_image_url)
                                            <div class="group relative cursor-pointer" onclick="openImageModal('{{ $gen->processed_image_url }}')">
                                                <img src="{{ $gen->processed_image_url }}" alt="Result" class="h-12 w-12 rounded object-cover border border-green-200 hover:opacity-75">
                                                <span class="absolute bottom-0 left-0 right-0 bg-green-900 bg-opacity-50 text-white text-[10px] text-center">Result</span>
                                            </div>
                                        @else
                                            <div class="h-12 w-12 rounded bg-gray-100 flex items-center justify-center text-gray-400 text-xs border border-dashed border-gray-300">
                                                Wait
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 font-semibold">
                                        {{ $gen->style_name }}
                                        <span class="text-xs font-normal text-gray-500 ml-1">({{ $gen->cost_in_credits }} credit)</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1 max-w-xs truncate" title="{{ $gen->prompt_used }}">
                                        {{ Str::limit($gen->prompt_used, 50) }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($gen->status === 'completed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    @elseif($gen->status === 'pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @elseif($gen->status === 'failed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Failed
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($gen->status) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $gen->created_at->diffForHumans() }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $generations->links() }}
                </div>

            </div>
        </div>
    </div>
</div>

<div id="imageModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" onclick="closeImageModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="flex justify-end absolute top-2 right-2 z-10">
                    <button type="button" onclick="closeImageModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-2 flex justify-center">
                    <img id="modalImageSrc" src="" alt="Full size" class="max-h-[80vh] w-auto object-contain rounded">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openImageModal(url) {
        const modal = document.getElementById('imageModal');
        const img = document.getElementById('modalImageSrc');
        img.src = url;
        modal.classList.remove('hidden');
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        const img = document.getElementById('modalImageSrc');
        modal.classList.add('hidden');
        setTimeout(() => { img.src = ''; }, 200); // Clear src after animation roughly finishes
    }

    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            closeImageModal();
        }
    });
</script>
@endsection
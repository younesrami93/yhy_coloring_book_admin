@extends('layouts.app')
@section('title', 'Manage Styles')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Styles Library</h1>
        <button onclick="openModal('createModal')" class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition shadow-lg shadow-slate-900/20 flex items-center gap-2">
            <i class="fas fa-plus"></i> Add New Style
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4 font-semibold">Preview</th>
                    <th class="px-6 py-4 font-semibold">Info</th>
                    <th class="px-6 py-4 font-semibold">Examples</th>
                    <th class="px-6 py-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                @foreach($styles as $style)
                <tr class="hover:bg-slate-50 transition group">
                    <td class="px-6 py-4">
                        <img src="{{ $style->thumbnail_url }}" class="w-16 h-16 rounded-lg object-cover border border-slate-200 shadow-sm">
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800 text-base mb-1">{{ $style->title }}</div>
                        <div class="text-xs text-slate-500 bg-slate-100 inline-block px-2 py-1 rounded">{{ number_format($style->usage_count) }} uses</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex -space-x-3">
                            <img src="{{ $style->example_before_url }}" class="w-10 h-10 rounded-full border-2 border-white object-cover" title="Before">
                            <img src="{{ $style->example_after_url }}" class="w-10 h-10 rounded-full border-2 border-white object-cover" title="Result">
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button 
                            onclick="openEditModal(this)"
                            data-id="{{ $style->id }}"
                            data-title="{{ $style->title }}"
                            data-prompt="{{ $style->prompt }}"
                            class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition mr-2">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <form action="{{ route('styles.destroy', $style) }}" method="POST" class="inline" onsubmit="return confirm('Archive this style?')">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-lg transition"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="createModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeModal('createModal')"></div>
        
        <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl p-8 overflow-y-auto transform transition-transform duration-300 translate-x-full" id="createPanel">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-xl font-bold text-slate-800">New Style</h2>
                <button onclick="closeModal('createModal')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form action="{{ route('styles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Title</label>
                    <input type="text" name="title" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Prompt</label>
                    <textarea name="prompt" rows="4" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>

                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-500 uppercase">Assets (Max 5MB)</p>
                    
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Thumbnail</label>
                        <input type="file" name="thumbnail" accept="image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Before Example</label>
                        <input type="file" name="example_before" accept="image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">After Example</label>
                        <input type="file" name="example_after" accept="image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-slate-900 text-white py-3 rounded-xl font-medium hover:bg-slate-800 transition shadow-lg shadow-slate-900/20">Create Style</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeModal('editModal')"></div>
        <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl p-8 overflow-y-auto transform transition-transform duration-300 translate-x-full" id="editPanel">
            
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-xl font-bold text-slate-800">Edit Style</h2>
                <button onclick="closeModal('editModal')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf 
                @method('PUT')
                
                <input type="hidden" id="edit_id">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Title</label>
                    <input type="text" name="title" id="edit_title" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Prompt</label>
                    <textarea name="prompt" id="edit_prompt" rows="4" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>

                <div class="p-4 bg-amber-50 rounded-xl border border-amber-100 text-amber-800 text-xs">
                    <i class="fas fa-info-circle mr-1"></i> Leave file inputs empty to keep current images.
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">New Thumbnail (Optional)</label>
                        <input type="file" name="thumbnail" accept="image/*" class="w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">New Before Example (Optional)</label>
                        <input type="file" name="example_before" accept="image/*" class="w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">New After Example (Optional)</label>
                        <input type="file" name="example_after" accept="image/*" class="w-full text-sm">
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700 transition shadow-lg shadow-blue-600/20">Update Style</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // General Modal Logic
    function openModal(id) {
        $('#' + id).removeClass('hidden');
        setTimeout(() => {
            $('#' + id + ' > div:last-child').removeClass('translate-x-full'); // Slide in panel
        }, 10);
    }

    function closeModal(id) {
        $('#' + id + ' > div:last-child').addClass('translate-x-full'); // Slide out panel
        setTimeout(() => {
            $('#' + id).addClass('hidden');
        }, 300);
    }

    // Specific Edit Logic
    function openEditModal(button) {
        // 1. Get data from clicked button
        let id = $(button).data('id');
        let title = $(button).data('title');
        let prompt = $(button).data('prompt');

        // 2. Populate Form
        $('#edit_id').val(id);
        $('#edit_title').val(title);
        $('#edit_prompt').val(prompt);

        // 3. Update Form Action URL dynamically
        // Assumes route is like /admin/styles/{id}
        let url = "{{ route('styles.update', ':id') }}";
        url = url.replace(':id', id);
        $('#editForm').attr('action', url);

        // 4. Open
        openModal('editModal');
    }
</script>
@endpush
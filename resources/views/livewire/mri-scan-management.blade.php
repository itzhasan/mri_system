<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">MRI Scan Management</h2>
            <p class="text-sm text-gray-500 mt-1">Manage scans, upload images, and assign doctors.</p>
        </div>
        @if(Auth::user()->isTechnician() || Auth::user()->isAdmin())
            <button wire:click="create"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg shadow-sm transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Scan
            </button>
        @endif
    </div>

    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-5 border-b border-gray-200">
            <div class="relative">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input wire:model.live="search" type="text" placeholder="Search by scan number or patient name..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Scan Number</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Body Part</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Images</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($scans as $scan)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $scan->scan_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $scan->patient->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ ucfirst($scan->body_part) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                                    @if($scan->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($scan->status == 'completed') bg-green-100 text-green-800
                                    @elseif($scan->status == 'reported') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($scan->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                                    @if($scan->priority == 'stat') bg-red-100 text-red-800
                                    @elseif($scan->priority == 'urgent') bg-orange-100 text-orange-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($scan->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="viewImages({{ $scan->id }})"
                                    class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View ({{ $scan->number_of_images }})
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                @if(Auth::user()->isTechnician() || Auth::user()->isAdmin())
                                    <button wire:click="edit({{ $scan->id }})"
                                        class="text-blue-600 hover:text-blue-800 font-medium mr-4">Edit</button>
                                    <button wire:click="delete({{ $scan->id }})"
                                        onclick="return confirm('Are you sure?')"
                                        class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No scans found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $scans->links() }}
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-gray-100 z-50 overflow-y-auto">
            <form wire:submit.prevent="save" class="min-h-screen flex flex-col">
                {{-- Sticky header --}}
                <div class="sticky top-0 bg-white border-b shadow-sm z-10">
                    <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center gap-4">
                        <div class="flex items-center gap-4">
                            <button type="button" wire:click="closeModal"
                                class="p-2 rounded-lg hover:bg-gray-100 text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    {{ $editMode ? 'Edit MRI Scan' : 'New MRI Scan' }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ $editMode ? 'Update scan details and manage images.' : 'Create a new scan entry and upload images.' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow disabled:opacity-50"
                                wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">Save Scan</span>
                                <span wire:loading wire:target="save">Saving…</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="flex-1 max-w-6xl mx-auto w-full px-6 py-6 space-y-6">
                    {{-- Patient & Assignment --}}
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Patient & Assignment</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Patient <span class="text-red-500">*</span></label>
                                <select wire:model="patient_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Patient</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}">{{ $patient->full_name }} ({{ $patient->patient_number }})</option>
                                    @endforeach
                                </select>
                                @error('patient_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Doctor</label>
                                <select wire:model="assigned_doctor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Doctor</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                                @error('assigned_doctor_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </section>

                    {{-- Scan Details --}}
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Scan Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Body Part <span class="text-red-500">*</span></label>
                                <select wire:model="body_part" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Body Part</option>
                                    <option value="brain">Brain</option>
                                    <option value="spine">Spine</option>
                                    <option value="chest">Chest</option>
                                    <option value="abdomen">Abdomen</option>
                                    <option value="pelvis">Pelvis</option>
                                    <option value="shoulder">Shoulder</option>
                                    <option value="knee">Knee</option>
                                    <option value="hip">Hip</option>
                                    <option value="ankle">Ankle</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('body_part') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Scan Type <span class="text-red-500">*</span></label>
                                <input wire:model="scan_type" type="text" placeholder="e.g., T1, T2, FLAIR"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('scan_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Scan Date &amp; Time <span class="text-red-500">*</span></label>
                                <input wire:model="scan_date" type="datetime-local"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('scan_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                <select wire:model="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="routine">Routine</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="stat">STAT</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select wire:model="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <label class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg w-full cursor-pointer hover:bg-gray-50">
                                    <input wire:model.live="contrast_used" type="checkbox" class="rounded">
                                    <span class="text-sm font-medium text-gray-700">Contrast Used</span>
                                </label>
                            </div>

                            @if($contrast_used)
                                <div class="md:col-span-2 lg:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Contrast Agent</label>
                                    <input wire:model="contrast_agent" type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            @endif
                        </div>
                    </section>

                    {{-- Clinical Information --}}
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Clinical Information</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Clinical Indication <span class="text-red-500">*</span></label>
                                <textarea wire:model="clinical_indication" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                @error('clinical_indication') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Technician Notes</label>
                                <textarea wire:model="technician_notes" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>
                        </div>
                    </section>

                    {{-- Images --}}
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-800">MRI Images</h4>
                            @if(count($images) > 0)
                                <span class="text-sm text-gray-500">{{ count($images) }} file(s) queued</span>
                            @endif
                        </div>

                        <label wire:key="file-input-wrapper-{{ $fileInputKey }}"
                            class="flex flex-col items-center justify-center px-6 py-10 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">
                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.9 5 5 0 019.9-1A5.5 5.5 0 0118 16H7z M12 12v8 m-4-4l4-4 4 4"></path>
                            </svg>
                            <p class="text-sm font-medium text-gray-700">Click to select images or drag &amp; drop</p>
                            <p class="text-xs text-gray-500 mt-1">You can add images in multiple batches (Max 10MB each)</p>
                            <input wire:model="newImages" type="file" multiple accept="image/*" class="hidden">
                        </label>

                        @error('newImages.*') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                        @error('images.*') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror

                        <div wire:loading wire:target="newImages" class="text-sm text-blue-600 mt-3 flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                                <path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            Uploading images…
                        </div>

                        @if(count($images) > 0)
                            <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                @foreach($images as $index => $file)
                                    <div class="relative group border border-gray-200 rounded-lg overflow-hidden bg-gray-50"
                                        wire:key="pending-{{ $fileInputKey }}-{{ $index }}">
                                        <img src="{{ $file->temporaryUrl() }}" alt="{{ $file->getClientOriginalName() }}"
                                            class="w-full h-32 object-cover">
                                        <div class="p-2">
                                            <p class="text-xs font-medium text-gray-700 truncate" title="{{ $file->getClientOriginalName() }}">
                                                {{ $file->getClientOriginalName() }}
                                            </p>
                                            <p class="text-xs text-gray-500">{{ number_format($file->getSize() / 1024, 1) }} KB</p>
                                        </div>
                                        <button type="button"
                                            wire:click.prevent="removePendingImage({{ $index }})"
                                            class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow opacity-0 group-hover:opacity-100 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>
            </form>
        </div>
    @endif

    @if($showImagesModal)
        <div class="fixed inset-0 bg-gray-900 z-50 overflow-y-auto">
            {{-- Sticky header --}}
            <div class="sticky top-0 bg-white border-b shadow-sm z-10">
                <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <button type="button" wire:click="closeModal"
                            class="p-2 rounded-lg hover:bg-gray-100 text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">MRI Images</h3>
                            <p class="text-sm text-gray-500">{{ count($currentScanImages) }} image(s)</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
                        Close
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="max-w-7xl mx-auto px-6 py-6">
                @if(count($currentScanImages) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                        @foreach($currentScanImages as $image)
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden group relative">
                                <a href="{{ $image->url }}" target="_blank" rel="noopener"
                                    class="block bg-gray-100 overflow-hidden">
                                    <img src="{{ $image->url }}" alt="{{ $image->file_name }}"
                                        class="w-full h-64 object-cover group-hover:scale-105 transition duration-300">
                                </a>
                                <div class="p-3">
                                    <p class="text-sm font-medium text-gray-800 truncate" title="{{ $image->file_name }}">
                                        {{ $image->file_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ number_format($image->file_size / 1024, 1) }} KB
                                    </p>
                                </div>
                                <button wire:click="deleteImage({{ $image->id }})"
                                    onclick="return confirm('Delete this image?')"
                                    class="absolute top-2 right-2 bg-red-500/90 hover:bg-red-600 text-white rounded-full p-2 shadow-lg opacity-0 group-hover:opacity-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-16 text-center">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-500">No images uploaded yet</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

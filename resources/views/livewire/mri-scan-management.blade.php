<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold">MRI Scan Management</h2>
        @if(Auth::user()->isTechnician() || Auth::user()->isAdmin())
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Add New Scan
            </button>
        @endif
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <input wire:model.live="search" type="text" placeholder="Search scans..." 
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scan Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Body Part</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Images</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($scans as $scan)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $scan->scan_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $scan->patient->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($scan->body_part) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($scan->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($scan->status == 'completed') bg-green-100 text-green-800
                                    @elseif($scan->status == 'reported') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($scan->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($scan->priority == 'stat') bg-red-100 text-red-800
                                    @elseif($scan->priority == 'urgent') bg-orange-100 text-orange-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($scan->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="viewImages({{ $scan->id }})" 
                                    class="text-blue-600 hover:text-blue-800">
                                    View ({{ $scan->number_of_images }})
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(Auth::user()->isTechnician() || Auth::user()->isAdmin())
                                    <button wire:click="edit({{ $scan->id }})" 
                                        class="text-blue-600 hover:text-blue-800 mr-3">Edit</button>
                                    <button wire:click="delete({{ $scan->id }})" 
                                        onclick="return confirm('Are you sure?')"
                                        class="text-red-600 hover:text-red-800">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No scans found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6">
            {{ $scans->links() }}
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">{{ $editMode ? 'Edit MRI Scan' : 'Add New MRI Scan' }}</h3>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-2 gap-4 max-h-96 overflow-y-auto p-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Patient</label>
                            <select wire:model="patient_id" class="w-full px-3 py-2 border rounded-lg">
                                <option value="">Select Patient</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->full_name }} ({{ $patient->patient_number }})</option>
                                @endforeach
                            </select>
                            @error('patient_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Doctor</label>
                            <select wire:model="assigned_doctor_id" class="w-full px-3 py-2 border rounded-lg">
                                <option value="">Select Doctor</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_doctor_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Body Part</label>
                            <select wire:model="body_part" class="w-full px-3 py-2 border rounded-lg">
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Scan Type</label>
                            <input wire:model="scan_type" type="text" placeholder="e.g., T1, T2, FLAIR" 
                                class="w-full px-3 py-2 border rounded-lg">
                            @error('scan_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Clinical Indication</label>
                            <textarea wire:model="clinical_indication" rows="2" 
                                class="w-full px-3 py-2 border rounded-lg"></textarea>
                            @error('clinical_indication') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Scan Date & Time</label>
                            <input wire:model="scan_date" type="datetime-local" class="w-full px-3 py-2 border rounded-lg">
                            @error('scan_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                            <select wire:model="priority" class="w-full px-3 py-2 border rounded-lg">
                                <option value="routine">Routine</option>
                                <option value="urgent">Urgent</option>
                                <option value="stat">STAT</option>
                            </select>
                            @error('priority') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select wire:model="status" class="w-full px-3 py-2 border rounded-lg">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center">
                                <input wire:model="contrast_used" type="checkbox" class="mr-2">
                                <span class="text-sm font-medium text-gray-700">Contrast Used</span>
                            </label>
                        </div>

                        @if($contrast_used)
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contrast Agent</label>
                                <input wire:model="contrast_agent" type="text" class="w-full px-3 py-2 border rounded-lg">
                            </div>
                        @endif

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Technician Notes</label>
                            <textarea wire:model="technician_notes" rows="3" 
                                class="w-full px-3 py-2 border rounded-lg"></textarea>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload MRI Images</label>
                            <input wire:model="images" type="file" multiple accept="image/*" 
                                class="w-full px-3 py-2 border rounded-lg">
                            <p class="text-sm text-gray-500 mt-1">You can select multiple images (Max 10MB each)</p>
                            @error('images.*') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 space-x-3">
                        <button type="button" wire:click="closeModal" 
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg">Cancel</button>
                        <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showImagesModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">MRI Images</h3>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                    @forelse($currentScanImages as $image)
                        <div class="relative border rounded-lg p-2">
                            <img src="{{ $image->url }}" alt="{{ $image->file_name }}" class="w-full h-48 object-cover rounded">
                            <p class="text-sm text-gray-600 mt-2 truncate">{{ $image->file_name }}</p>
                            <button wire:click="deleteImage({{ $image->id }})" 
                                onclick="return confirm('Are you sure?')"
                                class="absolute top-4 right-4 bg-red-500 hover:bg-red-600 text-white rounded-full p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @empty
                        <div class="col-span-3 text-center text-gray-500 py-8">
                            No images uploaded yet
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>

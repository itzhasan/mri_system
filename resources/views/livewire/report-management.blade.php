<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold">Report Management</h2>
        @if(Auth::user()->isDoctor() || Auth::user()->isAdmin())
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Create New Report
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
            <input wire:model.live="search" type="text" placeholder="Search reports..." 
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Report Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scan Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doctor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($reports as $report)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->report_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->mriScan->scan_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->mriScan->patient->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->doctor->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($report->status == 'draft') bg-yellow-100 text-yellow-800
                                    @elseif($report->status == 'final') bg-green-100 text-green-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->reported_at->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="edit({{ $report->id }})" 
                                    class="text-blue-600 hover:text-blue-800 mr-3">Edit</button>
                                @if(Auth::user()->isAdmin())
                                    <button wire:click="delete({{ $report->id }})" 
                                        onclick="return confirm('Are you sure?')"
                                        class="text-red-600 hover:text-red-800">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No reports found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6">
            {{ $reports->links() }}
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">{{ $editMode ? 'Edit Report' : 'Create New Report' }}</h3>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="space-y-4 max-h-96 overflow-y-auto p-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">MRI Scan</label>
                            <select wire:model="mri_scan_id" class="w-full px-3 py-2 border rounded-lg" 
                                {{ $editMode ? 'disabled' : '' }}>
                                <option value="">Select Scan</option>
                                @foreach($availableScans as $scan)
                                    <option value="{{ $scan->id }}">
                                        {{ $scan->scan_number }} - {{ $scan->patient->full_name }} - {{ ucfirst($scan->body_part) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mri_scan_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Findings</label>
                            <textarea wire:model="findings" rows="6" 
                                placeholder="Describe the findings from the MRI scan..."
                                class="w-full px-3 py-2 border rounded-lg"></textarea>
                            @error('findings') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Impression</label>
                            <textarea wire:model="impression" rows="4" 
                                placeholder="Summary and conclusion..."
                                class="w-full px-3 py-2 border rounded-lg"></textarea>
                            @error('impression') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recommendations</label>
                            <textarea wire:model="recommendations" rows="3" 
                                placeholder="Treatment recommendations or follow-up suggestions..."
                                class="w-full px-3 py-2 border rounded-lg"></textarea>
                            @error('recommendations') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Comparison Notes</label>
                            <textarea wire:model="comparison_notes" rows="3" 
                                placeholder="Comparison with previous scans if available..."
                                class="w-full px-3 py-2 border rounded-lg"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select wire:model="status" class="w-full px-3 py-2 border rounded-lg">
                                <option value="draft">Draft</option>
                                <option value="final">Final</option>
                                <option value="amended">Amended</option>
                            </select>
                            @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 space-x-3">
                        <button type="button" wire:click="closeModal" 
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg">Cancel</button>
                        <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Save Report</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

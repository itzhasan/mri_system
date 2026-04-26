<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Report Management</h2>
            <p class="text-sm text-gray-500 mt-1">Create and review radiology reports.</p>
        </div>
        @if(Auth::user()->isDoctor() || Auth::user()->isAdmin())
            <button wire:click="create"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg shadow-sm transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Report
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
                <input wire:model.live="search" type="text" placeholder="Search by report number, scan, or patient..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Report #</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Scan #</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Doctor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($reports as $report)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $report->report_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $report->mriScan->scan_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $report->mriScan->patient->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $report->doctor->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                                    @if($report->status == 'draft') bg-yellow-100 text-yellow-800
                                    @elseif($report->status == 'final') bg-green-100 text-green-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $report->reported_at->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <a href="{{ route('reports.print', $report) }}" target="_blank"
                                    class="text-gray-700 hover:text-gray-900 font-medium mr-4 inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Print
                                </a>
                                <button wire:click="edit({{ $report->id }})"
                                    class="text-blue-600 hover:text-blue-800 font-medium mr-4">Edit</button>
                                @if(Auth::user()->isAdmin())
                                    <button wire:click="delete({{ $report->id }})"
                                        onclick="return confirm('Are you sure?')"
                                        class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No reports found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $reports->links() }}
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-gray-900/50 z-50 overflow-y-auto flex items-start justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl my-8">
                <form wire:submit.prevent="save">
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $editMode ? 'Edit Report' : 'Create New Report' }}</h3>
                            <p class="text-sm text-gray-500">{{ $editMode ? 'Update the radiology report.' : 'Write a new radiology report for a completed scan.' }}</p>
                        </div>
                        <button type="button" wire:click="closeModal" class="p-2 rounded-lg hover:bg-gray-100 text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">MRI Scan <span class="text-red-500">*</span></label>
                            <select wire:model="mri_scan_id" {{ $editMode ? 'disabled' : '' }}
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100">
                                <option value="">Select Scan</option>
                                @foreach($availableScans as $scan)
                                    <option value="{{ $scan->id }}">
                                        {{ $scan->scan_number }} — {{ $scan->patient->full_name }} ({{ ucfirst($scan->body_part) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('mri_scan_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Findings <span class="text-red-500">*</span></label>
                            <textarea wire:model="findings" rows="6"
                                placeholder="Describe the findings from the MRI scan..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            @error('findings') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Impression <span class="text-red-500">*</span></label>
                            <textarea wire:model="impression" rows="4"
                                placeholder="Summary and conclusion..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            @error('impression') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recommendations</label>
                            <textarea wire:model="recommendations" rows="3"
                                placeholder="Treatment recommendations or follow-up suggestions..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            @error('recommendations') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Comparison Notes</label>
                            <textarea wire:model="comparison_notes" rows="3"
                                placeholder="Comparison with previous scans if available..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select wire:model="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="draft">Draft</option>
                                <option value="final">Final</option>
                                <option value="amended">Amended</option>
                            </select>
                            @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl flex justify-end gap-3">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-white text-gray-700">Cancel</button>
                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm">Save Report</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

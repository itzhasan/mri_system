<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold">Patient Management</h2>
        <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Add New Patient
        </button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <input wire:model.live="search" type="text" placeholder="Search patients..." 
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Age</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gender</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($patients as $patient)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $patient->patient_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $patient->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $patient->age }} years</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($patient->gender) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $patient->phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="edit({{ $patient->id }})" 
                                    class="text-blue-600 hover:text-blue-800 mr-3">Edit</button>
                                <button wire:click="delete({{ $patient->id }})" 
                                    onclick="return confirm('Are you sure?')"
                                    class="text-red-600 hover:text-red-800">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No patients found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6">
            {{ $patients->links() }}
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">{{ $editMode ? 'Edit Patient' : 'Add New Patient' }}</h3>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input wire:model="first_name" type="text" class="w-full px-3 py-2 border rounded-lg">
                            @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input wire:model="last_name" type="text" class="w-full px-3 py-2 border rounded-lg">
                            @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                            <input wire:model="date_of_birth" type="date" class="w-full px-3 py-2 border rounded-lg">
                            @error('date_of_birth') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                            <select wire:model="gender" class="w-full px-3 py-2 border rounded-lg">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            @error('gender') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <input wire:model="phone" type="text" class="w-full px-3 py-2 border rounded-lg">
                            @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input wire:model="email" type="email" class="w-full px-3 py-2 border rounded-lg">
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea wire:model="address" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
                            @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Name</label>
                            <input wire:model="emergency_contact_name" type="text" class="w-full px-3 py-2 border rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Phone</label>
                            <input wire:model="emergency_contact_phone" type="text" class="w-full px-3 py-2 border rounded-lg">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Medical History</label>
                            <textarea wire:model="medical_history" rows="3" class="w-full px-3 py-2 border rounded-lg"></textarea>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                            <textarea wire:model="allergies" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
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
</div>

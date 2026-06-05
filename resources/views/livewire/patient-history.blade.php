<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-full bg-blue-100 text-blue-700 text-lg font-semibold">
                {{ strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $patient->full_name }}</h1>
                <p class="text-sm text-gray-500">
                    #{{ $patient->patient_number }} ·
                    {{ ucfirst($patient->gender) }} ·
                    {{ $patient->age }} yrs ·
                    DOB {{ $patient->date_of_birth?->format('Y-m-d') }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ Auth::user()->isDoctor() ? route('scans') : route('patients') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">
                &larr; Back
            </a>
            <a href="{{ route('patients.history.pdf', $patient) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                ⬇ Download Full History (PDF)
            </a>
        </div>
    </div>

    {{-- Demographics / history card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-blue-600 mb-4">Patient Information</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div><span class="block text-xs text-gray-500">Phone</span>{{ $patient->phone ?: '—' }}</div>
            <div><span class="block text-xs text-gray-500">Email</span>{{ $patient->email ?: '—' }}</div>
            <div><span class="block text-xs text-gray-500">Address</span>{{ $patient->address ?: '—' }}</div>
            <div><span class="block text-xs text-gray-500">Emergency Contact</span>{{ $patient->emergency_contact_name ?: '—' }} {{ $patient->emergency_contact_phone ? '('.$patient->emergency_contact_phone.')' : '' }}</div>
            <div class="col-span-2"><span class="block text-xs text-gray-500">Allergies</span>{{ $patient->allergies ?: 'None recorded' }}</div>
        </div>
        @if($patient->medical_history)
            <div class="mt-4 pt-4 border-t border-gray-100 text-sm">
                <span class="block text-xs text-gray-500 mb-1">Medical History</span>
                <p class="whitespace-pre-wrap text-gray-700">{{ $patient->medical_history }}</p>
            </div>
        @endif
    </div>

    {{-- Scan & report timeline (medical history) --}}
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">
        Scan & Report History ({{ $scans->count() }})
    </h2>

    @forelse($scans as $scan)
        @include('partials.history-scan', ['scan' => $scan])
    @empty
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center text-gray-500">
            No scans recorded for this patient yet.
        </div>
    @endforelse
</div>

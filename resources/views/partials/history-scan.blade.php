@php
    $renderable = $scan->images->filter(fn ($img) => ! $img->is_dicom);
    $dicomImages = $scan->images->filter(fn ($img) => $img->is_dicom);
@endphp
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-4">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
        <div>
            <p class="text-base font-semibold text-gray-900">
                {{ ucfirst($scan->body_part) }} — {{ $scan->scan_type }}
            </p>
            <p class="text-sm text-gray-500">
                {{ $scan->scan_number }} · {{ $scan->scan_date?->format('M d, Y H:i') }}
                @if($scan->contrast_used) · with contrast @endif
            </p>
        </div>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
            @if($scan->status === 'reported') bg-green-100 text-green-800
            @elseif($scan->status === 'cancelled') bg-red-100 text-red-800
            @else bg-yellow-100 text-yellow-800 @endif">
            {{ ucfirst($scan->status) }}
        </span>
    </div>

    @if($scan->clinical_indication)
        <p class="text-sm text-gray-600 mb-4">
            <span class="font-medium text-gray-700">Clinical indication:</span> {{ $scan->clinical_indication }}
        </p>
    @endif

    {{-- Images --}}
    @if($renderable->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-4">
            @foreach($renderable as $img)
                <a href="{{ $img->url }}" target="_blank" class="block group">
                    <img src="{{ $img->url }}" alt="{{ $img->file_name }}"
                         class="w-full h-32 object-cover rounded-lg border border-gray-200 group-hover:ring-2 group-hover:ring-blue-400">
                    <p class="text-[11px] text-gray-500 mt-1 truncate">{{ $img->sequence_name ?: $img->file_name }}</p>
                </a>
            @endforeach
        </div>
    @endif

    @if($dicomImages->isNotEmpty())
        <div class="mb-4 text-xs text-gray-500">
            <span class="font-medium text-gray-600">DICOM files:</span>
            {{ $dicomImages->pluck('file_name')->implode(', ') }}
        </div>
    @endif

    {{-- Report --}}
    @if($scan->report)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <p class="text-sm font-semibold text-gray-800">
                    Report {{ $scan->report->report_number }}
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                        @if($scan->report->status === 'final') bg-green-100 text-green-800
                        @elseif($scan->report->status === 'amended') bg-blue-100 text-blue-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ ucfirst($scan->report->status) }}
                    </span>
                </p>
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.print', $scan->report) }}" target="_blank"
                       class="text-xs font-medium text-blue-600 hover:text-blue-800">View / Print</a>
                    <a href="{{ route('reports.pdf', $scan->report) }}"
                       class="text-xs font-medium text-blue-600 hover:text-blue-800">PDF</a>
                </div>
            </div>
            <p class="text-sm text-gray-600">
                <span class="font-medium text-gray-700">Impression:</span> {{ $scan->report->impression }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Dr. {{ $scan->report->doctor->name ?? '—' }} ·
                {{ $scan->report->reported_at?->format('M d, Y') }}
            </p>
        </div>
    @else
        <p class="mt-4 pt-4 border-t border-gray-100 text-sm text-gray-400 italic">No report yet.</p>
    @endif
</div>

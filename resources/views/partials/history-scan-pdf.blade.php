@php
    $cards = [];
    $unrendered = [];
    foreach ($scan->images as $img) {
        $uri = $img->preview_data_uri;
        if ($uri) {
            $cards[] = ['uri' => $uri, 'img' => $img];
        } elseif ($img->is_dicom) {
            $unrendered[] = $img;
        }
    }
@endphp
<div class="scan">
    <h2>{{ ucfirst($scan->body_part) }} — {{ $scan->scan_type }}</h2>
    <p class="sub">
        {{ $scan->scan_number }} · {{ $scan->scan_date?->format('M d, Y H:i') }}
        @if($scan->contrast_used) · with contrast @endif
        · Technician: {{ $scan->technician->name ?? '—' }}
        · Referring: {{ $scan->referringDoctor->name ?? '—' }}
    </p>

    @if($scan->clinical_indication)
        <div class="section-title">Clinical Indication</div>
        <p class="body-text">{{ $scan->clinical_indication }}</p>
    @endif

    @if($scan->report)
        <div class="section-title">
            Report {{ $scan->report->report_number }}
            <span class="badge badge-{{ $scan->report->status }}">{{ ucfirst($scan->report->status) }}</span>
        </div>
        <div class="section-title">Findings</div>
        <p class="body-text">{{ $scan->report->findings }}</p>
        <div class="section-title">Impression</div>
        <p class="body-text">{{ $scan->report->impression }}</p>
        @if($scan->report->recommendations)
            <div class="section-title">Recommendations</div>
            <p class="body-text">{{ $scan->report->recommendations }}</p>
        @endif
        <p class="sub" style="margin-top:8px;">
            Reported by Dr. {{ $scan->report->doctor->name ?? '—' }}
            @if($scan->report->reported_at) on {{ $scan->report->reported_at->format('Y-m-d') }} @endif
        </p>
    @else
        <p class="no-report">No report on file for this scan.</p>
    @endif

    @if(count($cards))
        <div class="section-title">Scan Images</div>
        <div class="images">
            @foreach($cards as $card)
                <img src="{{ $card['uri'] }}" alt="{{ $card['img']->file_name }}">
            @endforeach
        </div>
    @endif

    @if(count($unrendered))
        <div class="section-title">DICOM Files (compressed — not previewable)</div>
        <ul class="dicom-list">
            @foreach($unrendered as $img)
                <li>{{ $img->file_name }}@if($img->sequence_name) — {{ $img->sequence_name }} @endif</li>
            @endforeach
        </ul>
    @endif
</div>

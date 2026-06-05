<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report {{ $report->report_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 32px;
            background: #f3f4f6;
        }
        .page {
            background: #fff;
            max-width: 850px;
            margin: 0 auto;
            padding: 48px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0 0 4px 0;
            color: #1e40af;
        }
        .header p { margin: 0; font-size: 13px; color: #6b7280; }
        .meta {
            text-align: right;
            font-size: 13px;
            color: #4b5563;
        }
        .meta strong { color: #111827; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-final { background: #dcfce7; color: #166534; }
        .badge-draft { background: #fef9c3; color: #854d0e; }
        .badge-amended { background: #dbeafe; color: #1e40af; }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 32px;
            margin-bottom: 24px;
        }
        .field { font-size: 13px; }
        .field .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 2px;
        }
        .field .value { font-weight: 500; color: #111827; }
        .section {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }
        .section h2 {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2563eb;
            margin: 0 0 8px 0;
        }
        .section p {
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-wrap;
            margin: 0;
            color: #1f2937;
        }
        .signature {
            margin-top: 48px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }
        .signature .doctor {
            text-align: left;
        }
        .signature .doctor strong { display: block; font-size: 14px; }
        .signature .doctor span { font-size: 12px; color: #6b7280; }
        .signature .stamp {
            text-align: right;
            font-size: 12px;
            color: #6b7280;
        }
        .toolbar {
            max-width: 850px;
            margin: 0 auto 16px;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        .btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn:hover { background: #1d4ed8; }
        .btn-secondary { background: #fff; color: #374151; border: 1px solid #d1d5db; }
        .btn-secondary:hover { background: #f9fafb; }
        .images {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 8px;
        }
        .images .img-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
        }
        .images .img-card img {
            max-width: 100%;
            max-height: 260px;
            border-radius: 4px;
        }
        .images .img-card .caption {
            font-size: 11px;
            color: #6b7280;
            margin-top: 6px;
        }
        .dicom-list {
            font-size: 12px;
            color: #4b5563;
            margin: 8px 0 0;
            padding-left: 18px;
        }
        .dicom-list li { margin-bottom: 2px; }
        @media print {
            body { background: #fff; padding: 0; }
            .page { border: none; box-shadow: none; padding: 24px; max-width: none; }
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
    @unless($pdf ?? false)
    <div class="toolbar">
        <a href="{{ auth()->user()->isTechnician() ? route('scans') : route('reports') }}" class="btn btn-secondary">&larr; Back</a>
        <a href="{{ route('reports.pdf', $report) }}" class="btn btn-secondary">⬇ Download PDF</a>
        <button class="btn" onclick="window.print()">
            🖨 Print Report
        </button>
    </div>
    @endunless

    <div class="page">
        <div class="header">
            <div>
                <h1>MRI Radiology Report</h1>
                <p>MRI Archive System</p>
            </div>
            <div class="meta">
                <p><strong>Report #:</strong> {{ $report->report_number }}</p>
                <p><strong>Date:</strong> {{ $report->reported_at?->format('F d, Y H:i') ?? '—' }}</p>
                <p>
                    <span class="badge badge-{{ $report->status }}">{{ ucfirst($report->status) }}</span>
                </p>
            </div>
        </div>

        <div class="grid">
            <div class="field">
                <div class="label">Patient Name</div>
                <div class="value">{{ $report->mriScan->patient->full_name }}</div>
            </div>
            <div class="field">
                <div class="label">Patient ID / DOB</div>
                <div class="value">
                    #{{ $report->mriScan->patient->id }} —
                    {{ $report->mriScan->patient->date_of_birth?->format('Y-m-d') }}
                    ({{ $report->mriScan->patient->age ?? '—' }} y)
                </div>
            </div>
            <div class="field">
                <div class="label">Scan Number</div>
                <div class="value">{{ $report->mriScan->scan_number }}</div>
            </div>
            <div class="field">
                <div class="label">Scan Date</div>
                <div class="value">{{ $report->mriScan->scan_date?->format('Y-m-d H:i') }}</div>
            </div>
            <div class="field">
                <div class="label">Body Part / Type</div>
                <div class="value">{{ ucfirst($report->mriScan->body_part) }} — {{ $report->mriScan->scan_type }}</div>
            </div>
            <div class="field">
                <div class="label">Contrast</div>
                <div class="value">
                    {{ $report->mriScan->contrast_used ? 'Yes' : 'No' }}
                    @if($report->mriScan->contrast_used && $report->mriScan->contrast_agent)
                        ({{ $report->mriScan->contrast_agent }})
                    @endif
                </div>
            </div>
            <div class="field">
                <div class="label">Referring Doctor</div>
                <div class="value">{{ $report->mriScan->referringDoctor->name ?? '—' }}</div>
            </div>
            <div class="field">
                <div class="label">MRI Technician</div>
                <div class="value">{{ $report->mriScan->technician->name ?? '—' }}</div>
            </div>
        </div>

        <div class="section">
            <h2>Clinical Indication</h2>
            <p>{{ $report->mriScan->clinical_indication ?: '—' }}</p>
        </div>

        <div class="section">
            <h2>Findings</h2>
            <p>{{ $report->findings }}</p>
        </div>

        <div class="section">
            <h2>Impression</h2>
            <p>{{ $report->impression }}</p>
        </div>

        @if($report->recommendations)
            <div class="section">
                <h2>Recommendations</h2>
                <p>{{ $report->recommendations }}</p>
            </div>
        @endif

        @if($report->comparison_notes)
            <div class="section">
                <h2>Comparison Notes</h2>
                <p>{{ $report->comparison_notes }}</p>
            </div>
        @endif

        @php
            $scanImages = $report->mriScan->images ?? collect();
            $cards = [];
            $unrendered = [];
            foreach ($scanImages as $img) {
                $uri = $img->preview_data_uri;
                if ($uri) {
                    $cards[] = ['uri' => $uri, 'img' => $img];
                } elseif ($img->is_dicom) {
                    $unrendered[] = $img;
                }
            }
        @endphp

        @if($scanImages->isNotEmpty())
            <div class="section">
                <h2>Scan Images</h2>

                @if(count($cards))
                    <div class="images">
                        @foreach($cards as $card)
                            <div class="img-card">
                                <img src="{{ $card['uri'] }}" alt="{{ $card['img']->file_name }}">
                                <div class="caption">
                                    {{ $card['img']->sequence_name ?: $card['img']->file_name }}
                                    @if($card['img']->slice_number) — slice {{ $card['img']->slice_number }} @endif
                                    @if($card['img']->is_dicom) · DICOM @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(count($unrendered))
                    <p style="font-size:12px;color:#6b7280;margin:12px 0 0;">
                        DICOM files (compressed — view in the imaging workstation):
                    </p>
                    <ul class="dicom-list">
                        @foreach($unrendered as $img)
                            <li>
                                {{ $img->file_name }}
                                @if($img->sequence_name) — {{ $img->sequence_name }} @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <div class="signature">
            <div class="doctor">
                <strong>Dr. {{ $report->doctor->name }}</strong>
                <span>Reporting Radiologist</span>
                @if($report->finalized_at)
                    <span>Finalized: {{ $report->finalized_at->format('Y-m-d H:i') }}</span>
                @endif
            </div>
            <div class="stamp">
                Generated {{ now()->format('Y-m-d H:i') }}<br>
                MRI Archive System
            </div>
        </div>
    </div>
</body>
</html>

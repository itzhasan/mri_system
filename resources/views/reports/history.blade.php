<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical History — {{ $patient->full_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 24px;
            font-size: 13px;
        }
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h1 { font-size: 22px; margin: 0 0 4px; color: #1e40af; }
        .header p { margin: 0; font-size: 12px; color: #6b7280; }
        .patient-grid {
            margin: 12px 0 20px;
            width: 100%;
        }
        .patient-grid td {
            font-size: 12px;
            padding: 3px 8px 3px 0;
            vertical-align: top;
        }
        .patient-grid .label { color: #6b7280; width: 130px; }
        .patient-grid .value { color: #111827; font-weight: 600; }
        .mh-block {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 20px;
            font-size: 12px;
            white-space: pre-wrap;
        }
        .scan {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .scan h2 {
            font-size: 15px;
            margin: 0 0 2px;
            color: #111827;
        }
        .scan .sub { font-size: 11px; color: #6b7280; margin: 0 0 10px; }
        .section-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #2563eb;
            margin: 12px 0 4px;
            font-weight: 700;
        }
        .body-text { font-size: 12px; line-height: 1.5; white-space: pre-wrap; margin: 0; }
        .images { margin-top: 6px; }
        .images img {
            max-width: 230px;
            max-height: 200px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            margin: 0 8px 8px 0;
        }
        .dicom-list { font-size: 11px; color: #4b5563; margin: 4px 0 0; padding-left: 16px; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-final { background: #dcfce7; color: #166534; }
        .badge-draft { background: #fef9c3; color: #854d0e; }
        .badge-amended { background: #dbeafe; color: #1e40af; }
        .no-report { font-size: 12px; color: #9ca3af; font-style: italic; }
        .footer { margin-top: 24px; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Patient Medical History</h1>
        <p>MRI Archive System</p>
    </div>

    <table class="patient-grid">
        <tr>
            <td class="label">Patient Name</td><td class="value">{{ $patient->full_name }}</td>
            <td class="label">Patient #</td><td class="value">{{ $patient->patient_number }}</td>
        </tr>
        <tr>
            <td class="label">Date of Birth</td><td class="value">{{ $patient->date_of_birth?->format('Y-m-d') }} ({{ $patient->age }} y)</td>
            <td class="label">Gender</td><td class="value">{{ ucfirst($patient->gender) }}</td>
        </tr>
        <tr>
            <td class="label">Phone</td><td class="value">{{ $patient->phone ?: '—' }}</td>
            <td class="label">Allergies</td><td class="value">{{ $patient->allergies ?: 'None recorded' }}</td>
        </tr>
    </table>

    @if($patient->medical_history)
        <div class="section-title">Medical History Notes</div>
        <div class="mh-block">{{ $patient->medical_history }}</div>
    @endif

    <div class="section-title">Scans &amp; Reports ({{ $scans->count() }})</div>

    @forelse($scans as $scan)
        @include('partials.history-scan-pdf', ['scan' => $scan])
    @empty
        <p class="no-report">No scans recorded for this patient.</p>
    @endforelse

    <div class="footer">
        Generated {{ now()->format('Y-m-d H:i') }} · MRI Archive System
    </div>
</body>
</html>

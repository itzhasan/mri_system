<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;

class PatientHistoryController extends Controller
{
    public function pdf(Patient $patient)
    {
        $patient->load([
            'mriScans' => fn ($q) => $q->orderByDesc('scan_date'),
            'mriScans.images',
            'mriScans.technician',
            'mriScans.referringDoctor',
            'mriScans.report.doctor',
        ]);

        $pdf = Pdf::loadView('reports.history', [
            'patient' => $patient,
            'scans' => $patient->mriScans,
        ])->setPaper('a4');

        return $pdf->download('medical-history-' . $patient->patient_number . '.pdf');
    }
}

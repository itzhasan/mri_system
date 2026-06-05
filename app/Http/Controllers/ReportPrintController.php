<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReportPrintController extends Controller
{
    public function show(Report $report)
    {
        return view('reports.print', $this->data($report));
    }

    public function pdf(Report $report)
    {
        $data = $this->data($report);
        $data['pdf'] = true;

        $pdf = Pdf::loadView('reports.print', $data)->setPaper('a4');

        return $pdf->download('report-' . $report->report_number . '.pdf');
    }

    /**
     * Authorize access and eager-load everything the report view needs.
     */
    private function data(Report $report): array
    {
        $user = Auth::user();

        if ($user->isDoctor() && $report->doctor_id !== $user->id) {
            abort(403, 'You can only access your own reports.');
        }

        $report->load([
            'mriScan.patient',
            'mriScan.technician',
            'mriScan.referringDoctor',
            'mriScan.images',
            'doctor',
        ]);

        return compact('report');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class ReportPrintController extends Controller
{
    public function show(Report $report)
    {
        $user = Auth::user();

        if ($user->isDoctor() && $report->doctor_id !== $user->id) {
            abort(403, 'You can only print your own reports.');
        }

        $report->load(['mriScan.patient', 'mriScan.technician', 'mriScan.referringDoctor', 'doctor']);

        return view('reports.print', compact('report'));
    }
}

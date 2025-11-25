<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MriScan;
use App\Models\Patient;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        $stats = [
            'total_scans' => MriScan::count(),
            'pending_scans' => MriScan::where('status', 'pending')->count(),
            'total_patients' => Patient::count(),
            'completed_reports' => Report::where('status', 'final')->count(),
        ];

        if ($user->isDoctor()) {
            $recentScans = MriScan::where('assigned_doctor_id', $user->id)
                ->with(['patient', 'technician'])
                ->latest()
                ->take(5)
                ->get();
        } elseif ($user->isTechnician()) {
            $recentScans = MriScan::where('mri_technician_id', $user->id)
                ->with(['patient', 'assignedDoctor'])
                ->latest()
                ->take(5)
                ->get();
        } else {
            $recentScans = MriScan::with(['patient', 'technician', 'assignedDoctor'])
                ->latest()
                ->take(5)
                ->get();
        }

        return view('livewire.dashboard', compact('stats', 'recentScans'))
            ->layout('layouts.app');  // Add this line
    }
}
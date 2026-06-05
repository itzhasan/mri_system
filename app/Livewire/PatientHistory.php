<?php

namespace App\Livewire;

use App\Models\Patient;
use Livewire\Component;

class PatientHistory extends Component
{
    public Patient $patient;

    public function mount(Patient $patient)
    {
        $this->patient = $patient;
    }

    public function render()
    {
        $this->patient->load([
            'mriScans' => fn ($q) => $q->orderByDesc('scan_date'),
            'mriScans.images',
            'mriScans.technician',
            'mriScans.referringDoctor',
            'mriScans.report.doctor',
        ]);

        return view('livewire.patient-history', [
            'scans' => $this->patient->mriScans,
        ])->layout('layouts.app');
    }
}

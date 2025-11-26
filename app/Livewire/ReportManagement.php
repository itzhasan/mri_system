<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Report;
use App\Models\MriScan;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class ReportManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    
    public $reportId;
    public $mri_scan_id;
    public $findings;
    public $impression;
    public $recommendations;
    public $comparison_notes;
    public $status = 'draft';

    protected $rules = [
        'mri_scan_id' => 'required|exists:mri_scans,id',
        'findings' => 'required|string',
        'impression' => 'required|string',
        'recommendations' => 'nullable|string',
        'comparison_notes' => 'nullable|string',
        'status' => 'required|in:draft,final,amended',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create($scanId = null)
    {
        $this->resetForm();
        $this->mri_scan_id = $scanId;
        $this->showModal = true;
        $this->editMode = false;
    }

    public function edit($id)
    {
        $report = Report::findOrFail($id);
        $this->reportId = $report->id;
        $this->mri_scan_id = $report->mri_scan_id;
        $this->findings = $report->findings;
        $this->impression = $report->impression;
        $this->recommendations = $report->recommendations;
        $this->comparison_notes = $report->comparison_notes;
        $this->status = $report->status;
        
        $this->showModal = true;
        $this->editMode = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'mri_scan_id' => $this->mri_scan_id,
            'doctor_id' => Auth::id(),
            'findings' => $this->findings,
            'impression' => $this->impression,
            'recommendations' => $this->recommendations,
            'comparison_notes' => $this->comparison_notes,
            'status' => $this->status,
        ];

        if ($this->status === 'final') {
            $data['finalized_at'] = now();
        }

        if ($this->editMode) {
            Report::find($this->reportId)->update($data);
            session()->flash('message', 'Report updated successfully.');
        } else {
            $data['report_number'] = 'REP' . date('Ymd') . str_pad(Report::count() + 1, 4, '0', STR_PAD_LEFT);
            $data['reported_at'] = now();
            $report = Report::create($data);
            
            // Update scan status
            $scan = MriScan::find($this->mri_scan_id);
            $scan->update(['status' => 'reported']);
            
            // Send notification to technician
            Notification::create([
                'user_id' => $scan->mri_technician_id,
                'mri_scan_id' => $scan->id,
                'report_id' => $report->id,
                'title' => 'Report Completed',
                'message' => 'Report for scan ' . $scan->scan_number . ' has been completed.',
                'type' => 'report_ready',
            ]);
            
            session()->flash('message', 'Report created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Report::find($id)->delete();
        session()->flash('message', 'Report deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reportId = null;
        $this->mri_scan_id = null;
        $this->findings = '';
        $this->impression = '';
        $this->recommendations = '';
        $this->comparison_notes = '';
        $this->status = 'draft';
    }

    public function render()
    {
        $user = Auth::user();
        
        $query = Report::with(['mriScan.patient', 'doctor']);
        
        if ($user->isDoctor()) {
            $query->where('doctor_id', $user->id);
        }
        
        if ($this->search) {
            $query->where('report_number', 'like', '%'.$this->search.'%');
        }
        
        $reports = $query->latest()->paginate(10);
        
        $availableScans = MriScan::with('patient')
            ->where('assigned_doctor_id', $user->id)
            ->whereDoesntHave('report')
            ->where('status', '!=', 'cancelled')
            ->get();

        return view('livewire.report-management', compact('reports', 'availableScans'))
            ->layout('layouts.app');
    }
}
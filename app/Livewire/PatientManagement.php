<?php

namespace App\Livewire;

use App\Models\MriScan;
use App\Models\Report;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Notification;

class PatientManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;

    public $patientId;
    public $first_name;
    public $last_name;
    public $date_of_birth;
    public $gender;
    public $phone;
    public $email;
    public $address;
    public $emergency_contact_name;
    public $emergency_contact_phone;
    public $medical_history;
    public $allergies;

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'date_of_birth' => 'required|date',
        'gender' => 'required|in:male,female,other',
        'phone' => 'required|string|max:20',
        'email' => 'nullable|email|max:255',
        'address' => 'nullable|string',
        'emergency_contact_name' => 'nullable|string|max:255',
        'emergency_contact_phone' => 'nullable|string|max:20',
        'medical_history' => 'nullable|string',
        'allergies' => 'nullable|string',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
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
            $query->where('report_number', 'like', '%' . $this->search . '%');
        }

        $reports = $query->latest()->paginate(10);

        $availableScans = MriScan::with('patient')
            ->where('assigned_doctor_id', $user->id)
            ->whereDoesntHave('report')
            ->where('status', '!=', 'cancelled')
            ->get();

        return view('livewire.report-management', compact('reports', 'availableScans'));
    }
}

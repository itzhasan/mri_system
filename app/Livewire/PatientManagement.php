<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Patient;

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
        $this->editMode = false;
    }

    public function edit($id)
    {
        $patient = Patient::findOrFail($id);

        $this->patientId = $patient->id;
        $this->first_name = $patient->first_name;
        $this->last_name = $patient->last_name;
        $this->date_of_birth = optional($patient->date_of_birth)->format('Y-m-d');
        $this->gender = $patient->gender;
        $this->phone = $patient->phone;
        $this->email = $patient->email;
        $this->address = $patient->address;
        $this->emergency_contact_name = $patient->emergency_contact_name;
        $this->emergency_contact_phone = $patient->emergency_contact_phone;
        $this->medical_history = $patient->medical_history;
        $this->allergies = $patient->allergies;

        $this->showModal = true;
        $this->editMode = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'medical_history' => $this->medical_history,
            'allergies' => $this->allergies,
        ];

        if ($this->editMode && $this->patientId) {
            $patient = Patient::findOrFail($this->patientId);
            $patient->update($data);
            session()->flash('message', 'Patient updated successfully.');
        } else {
            // Generate a simple incremental patient number (e.g., P20250004)
            $nextNumber = str_pad(Patient::withTrashed()->count() + 1, 4, '0', STR_PAD_LEFT);
            $data['patient_number'] = 'P' . now()->format('Y') . $nextNumber;

            Patient::create($data);
            session()->flash('message', 'Patient created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();

        session()->flash('message', 'Patient deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->patientId = null;
        $this->first_name = '';
        $this->last_name = '';
        $this->date_of_birth = '';
        $this->gender = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->emergency_contact_name = '';
        $this->emergency_contact_phone = '';
        $this->medical_history = '';
        $this->allergies = '';
        $this->editMode = false;
    }

    public function render()
    {
        $patients = Patient::query()
            ->when($this->search, function ($query) {
                $query->where('patient_number', 'like', '%' . $this->search . '%')
                    ->orWhere('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.patient-management', compact('patients'))
            ->layout('layouts.app');
    }
}

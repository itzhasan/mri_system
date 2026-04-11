<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\MriScan;
use App\Models\Patient;
use App\Models\User;
use App\Models\MriImage;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MriScanManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $showImagesModal = false;
    
    public $scanId;
    public $patient_id;
    public $assigned_doctor_id;
    public $body_part;
    public $scan_type;
    public $clinical_indication;
    public $scan_date;
    public $status = 'pending';
    public $technician_notes;
    public $priority = 'routine';
    public $contrast_used = false;
    public $contrast_agent;
    public $images = [];
    public $newImages = [];
    public $fileInputKey = 0;
    public $currentScanImages = [];

    protected $rules = [
        'patient_id' => 'required|exists:patients,id',
        'assigned_doctor_id' => 'nullable|exists:users,id',
        'body_part' => 'required|string',
        'scan_type' => 'required|string',
        'clinical_indication' => 'required|string',
        'scan_date' => 'required|date',
        'status' => 'required|string',
        'technician_notes' => 'nullable|string',
        'priority' => 'required|string',
        'contrast_used' => 'boolean',
        'contrast_agent' => 'nullable|string',
        'images.*' => 'nullable|image|max:10240',
        'newImages.*' => 'nullable|image|max:10240',
    ];

    public function updatedNewImages()
    {
        $this->validateOnly('newImages.*');

        foreach ($this->newImages as $file) {
            $this->images[] = $file;
        }

        $this->newImages = [];
        $this->fileInputKey++;
    }

    public function removePendingImage($index)
    {
        if (isset($this->images[$index])) {
            unset($this->images[$index]);
            $this->images = array_values($this->images);
        }
    }

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
        $scan = MriScan::findOrFail($id);
        $this->scanId = $scan->id;
        $this->patient_id = $scan->patient_id;
        $this->assigned_doctor_id = $scan->assigned_doctor_id;
        $this->body_part = $scan->body_part;
        $this->scan_type = $scan->scan_type;
        $this->clinical_indication = $scan->clinical_indication;
        $this->scan_date = $scan->scan_date->format('Y-m-d\TH:i');
        $this->status = $scan->status;
        $this->technician_notes = $scan->technician_notes;
        $this->priority = $scan->priority;
        $this->contrast_used = $scan->contrast_used;
        $this->contrast_agent = $scan->contrast_agent;
        
        $this->showModal = true;
        $this->editMode = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'patient_id' => $this->patient_id,
            'mri_technician_id' => Auth::id(),
            'assigned_doctor_id' => $this->assigned_doctor_id,
            'body_part' => $this->body_part,
            'scan_type' => $this->scan_type,
            'clinical_indication' => $this->clinical_indication,
            'scan_date' => $this->scan_date,
            'status' => $this->status,
            'technician_notes' => $this->technician_notes,
            'priority' => $this->priority,
            'contrast_used' => $this->contrast_used,
            'contrast_agent' => $this->contrast_agent,
        ];

        if ($this->editMode) {
            $scan = MriScan::find($this->scanId);
            $scan->update($data);
        } else {
            $data['scan_number'] = 'MRI' . date('Ymd') . str_pad(MriScan::count() + 1, 4, '0', STR_PAD_LEFT);
            $scan = MriScan::create($data);
        }

        // Upload images
        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                $path = $image->store('mri-images', 'public');
                MriImage::create([
                    'mri_scan_id' => $scan->id,
                    'file_name' => $image->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $image->getClientOriginalExtension(),
                    'file_size' => $image->getSize(),
                ]);
            }
            $scan->update(['number_of_images' => $scan->images()->count()]);
        }

        // Send notification to assigned doctor
        if ($this->assigned_doctor_id && !$this->editMode) {
            Notification::create([
                'user_id' => $this->assigned_doctor_id,
                'mri_scan_id' => $scan->id,
                'title' => 'New MRI Scan Assigned',
                'message' => 'A new MRI scan has been assigned to you.',
                'type' => 'scan_assigned',
            ]);
        }

        session()->flash('message', $this->editMode ? 'Scan updated successfully.' : 'Scan created successfully.');
        $this->closeModal();
    }

    public function viewImages($id)
    {
        $this->scanId = $id;
        $this->currentScanImages = MriImage::where('mri_scan_id', $id)->get();
        $this->showImagesModal = true;
    }

    public function deleteImage($imageId)
    {
        $image = MriImage::find($imageId);
        Storage::disk('public')->delete($image->file_path);
        $image->delete();
        
        $scan = MriScan::find($this->scanId);
        $scan->update(['number_of_images' => $scan->images()->count()]);
        
        $this->currentScanImages = MriImage::where('mri_scan_id', $this->scanId)->get();
        session()->flash('message', 'Image deleted successfully.');
    }

    public function delete($id)
    {
        $scan = MriScan::find($id);
        foreach ($scan->images as $image) {
            Storage::disk('public')->delete($image->file_path);
            $image->delete();
        }
        $scan->delete();
        session()->flash('message', 'Scan deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showImagesModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->scanId = null;
        $this->patient_id = null;
        $this->assigned_doctor_id = null;
        $this->body_part = '';
        $this->scan_type = '';
        $this->clinical_indication = '';
        $this->scan_date = '';
        $this->status = 'pending';
        $this->technician_notes = '';
        $this->priority = 'routine';
        $this->contrast_used = false;
        $this->contrast_agent = '';
        $this->images = [];
        $this->newImages = [];
        $this->fileInputKey++;
        $this->currentScanImages = [];
    }

    public function render()
    {
        $user = Auth::user();
        
        $query = MriScan::with(['patient', 'technician', 'assignedDoctor']);
        
        if ($user->isDoctor()) {
            $query->where('assigned_doctor_id', $user->id);
        } elseif ($user->isTechnician()) {
            $query->where('mri_technician_id', $user->id);
        }
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('scan_number', 'like', '%'.$this->search.'%')
                  ->orWhereHas('patient', function($q) {
                      $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%');
                  });
            });
        }
        
        $scans = $query->latest()->paginate(10);
        $patients = Patient::all();
        $doctors = User::where('role', 'doctor')->where('is_active', true)->get();

        return view('livewire.mri-scan-management', compact('scans', 'patients', 'doctors'))
            ->layout('layouts.app');
    }
}
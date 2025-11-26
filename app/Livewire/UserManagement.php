<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;

    public $userId;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $role = 'mri_technician';
    public $phone;
    public $specialization;
    public $license_number;
    public $is_active = true;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin,doctor,mri_technician',
            'phone' => 'nullable|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];

        if ($this->editMode) {
            $rules['email'] = 'required|email|max:255|unique:users,email,' . $this->userId;
            $rules['password'] = 'nullable|string|min:8|confirmed';
        } else {
            $rules['email'] = 'required|email|max:255|unique:users,email';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        return $rules;
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
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = $user->role;
        $this->phone = $user->phone;
        $this->specialization = $user->specialization;
        $this->license_number = $user->license_number;
        $this->is_active = $user->is_active;
        
        $this->showModal = true;
        $this->editMode = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone' => $this->phone,
            'specialization' => $this->specialization,
            'license_number' => $this->license_number,
            'is_active' => $this->is_active,
        ];

        if ($this->editMode) {
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }
            User::find($this->userId)->update($data);
            session()->flash('message', 'User updated successfully.');
        } else {
            $data['password'] = Hash::make($this->password);
            User::create($data);
            session()->flash('message', 'User created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        // Prevent deleting yourself
        if ($id == Auth::id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        $user = User::find($id);
        if ($user) {
            $user->delete();
            session()->flash('message', 'User deleted successfully.');
        }
    }

    public function toggleActive($id)
    {
        // Prevent deactivating yourself
        if ($id == Auth::id()) {
            session()->flash('error', 'You cannot deactivate your own account.');
            return;
        }

        $user = User::find($id);
        if ($user) {
            $user->update(['is_active' => !$user->is_active]);
            session()->flash('message', 'User status updated successfully.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = 'mri_technician';
        $this->phone = '';
        $this->specialization = '';
        $this->license_number = '';
        $this->is_active = true;
    }

    public function render()
    {
        $query = User::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        $users = $query->latest()->paginate(10);

        return view('livewire.user-management', compact('users'))
            ->layout('layouts.app');
    }
}


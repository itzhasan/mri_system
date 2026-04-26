<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatManagement extends Component
{
    public $selectedUserId = null;
    public $messages = [];
    public $body = '';
    public $search = '';

    public function mount($userId = null)
    {
        if ($userId) {
            $this->selectUser((int) $userId);
        }
    }

    public function selectUser($userId)
    {
        $this->selectedUserId = $userId;
        $this->loadMessages();

        Message::where('sender_id', $userId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function loadMessages()
    {
        if (!$this->selectedUserId) {
            $this->messages = [];
            return;
        }

        $authId = Auth::id();
        $other = $this->selectedUserId;

        $this->messages = Message::with('sender')
            ->where(function ($q) use ($authId, $other) {
                $q->where('sender_id', $authId)->where('receiver_id', $other);
            })
            ->orWhere(function ($q) use ($authId, $other) {
                $q->where('sender_id', $other)->where('receiver_id', $authId);
            })
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    public function sendMessage()
    {
        $this->validate([
            'body' => 'required|string|max:2000',
            'selectedUserId' => 'required|exists:users,id',
        ]);

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->selectedUserId,
            'body' => $this->body,
        ]);

        $this->body = '';
        $this->loadMessages();
    }

    public function getContactsProperty()
    {
        $user = Auth::user();

        // Doctors talk to technicians and admins; technicians talk to doctors and admins; admin talks to everyone.
        $query = User::query()->where('id', '!=', $user->id);

        if ($user->isDoctor()) {
            $query->whereIn('role', ['mri_technician', 'admin']);
        } elseif ($user->isTechnician()) {
            $query->whereIn('role', ['doctor', 'admin']);
        }

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        $contacts = $query->orderBy('name')->get();

        $unreadCounts = Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->select('sender_id', DB::raw('count(*) as total'))
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        $lastMessages = Message::where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function ($m) use ($user) {
                return $m->sender_id === $user->id ? $m->receiver_id : $m->sender_id;
            })
            ->map(fn ($group) => $group->first());

        foreach ($contacts as $c) {
            $c->unread_count = $unreadCounts[$c->id] ?? 0;
            $c->last_message = $lastMessages[$c->id] ?? null;
        }

        return $contacts;
    }

    public function render()
    {
        $selectedUser = $this->selectedUserId ? User::find($this->selectedUserId) : null;

        return view('livewire.chat-management', [
            'contacts' => $this->contacts,
            'selectedUser' => $selectedUser,
        ])->layout('layouts.app');
    }
}

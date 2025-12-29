<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;

#[Layout('layouts.app')]
#[Title('Admin Management')]
class AdminIndex extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    public $userId;
    public $name;
    public $email;
    public $password;
    public $phone_number;
    public $role = 2; 
    public $avatar;
    
    public $selectedUser;
    public $adminIdBeingDeleted; 
    public bool $isEdit = false;

    protected $allowedRoles = [2, 7, 9];

    public function resetFields()
    {
        $this->reset([
            'userId', 'name', 'email', 'password', 
            'phone_number', 'avatar', 'selectedUser', 
            'adminIdBeingDeleted'
        ]);
        $this->role = 2; 
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetFields();
        $this->isEdit = false;
        $this->modal('admin-modal')->show();
    }

    public function edit(string $id)
    {
        $this->resetFields();
        $user = User::findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone_number = $user->phone_number;
        $this->role = $user->role;
        $this->password = '';

        $this->isEdit = true;
        $this->modal('admin-modal')->show();
    }

    public function showDetail(string $id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->modal('detail-modal')->show();
    }

    public function confirmDelete(string $id)
    {
        $this->adminIdBeingDeleted = $id;
        $this->modal('delete-admin-modal')->show();
    }

    public function delete()
    {
        if ($this->adminIdBeingDeleted) {
            User::findOrFail($this->adminIdBeingDeleted)->delete();
            $this->modal('delete-admin-modal')->close();
            $this->adminIdBeingDeleted = null;
        }
    }

    public function save()
    {
        $this->persist();
        $this->modal('admin-modal')->close();
    }

    public function saveAndCreateAnother()
    {
        $this->persist();
        $this->resetFields();
        $this->isEdit = false;
    }

    private function generateUserCode($role)
    {
        $prefix = match((int)$role) {
            2 => 'ADM',
            7 => 'ACC',
            9 => 'WHS',
            default => 'USR'
        };
        return $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
    }

    protected function persist()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)],
            'phone_number' => 'nullable|string|max:20',
            'role' => ['required', Rule::in($this->allowedRoles)],
            'password' => $this->isEdit ? 'nullable|min:8' : 'required|min:8',
            'avatar' => 'nullable|image|max:1024',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'role' => $this->role,
        ];

        if (!empty($this->password)) {
            $data['password_hash'] = Hash::make($this->password);
        }

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('avatars', 'public');
        }

        if ($this->isEdit) {
            User::findOrFail($this->userId)->update($data);
        } else {
            $data['user_code'] = $this->generateUserCode($this->role);
            User::create($data);
        }
    }

    public function render()
    {
        return view('livewire.admin.admin-index', [
            'admins' => User::whereIn('role', $this->allowedRoles) 
                ->orderByDesc('created_at')
                ->paginate(10),
        ]);
    }
}
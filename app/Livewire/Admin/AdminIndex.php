<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Admin Management')]
class AdminIndex extends Component
{
    use WithPagination, WithFileUploads;

    // Form Properties (Sesuai gambar referensi)
    public $adminId;
    public $name;
    public $email;
    public $email_verified_at;
    public $password;
    public $phone;
    public $avatar; 
    public $is_active = true;
    
    public bool $isEdit = false;

    protected $paginationTheme = 'tailwind';

    /**
     * Reset form ke kondisi awal
     */
    public function resetFields()
    {
        $this->reset(['adminId', 'name', 'email', 'email_verified_at', 'password', 'phone', 'avatar']);
        $this->is_active = true;
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
        $admin = Admin::findOrFail($id);
        
        $this->adminId = $admin->id;
        $this->name = $admin->name;
        $this->email = $admin->email;
        $this->email_verified_at = $admin->email_verified_at ? date('Y-m-d\TH:i', strtotime($admin->email_verified_at)) : null;
        $this->phone = $admin->phone;
        $this->is_active = (bool) $admin->is_active;
        $this->password = ''; 

        $this->isEdit = true;
        $this->modal('admin-modal')->show();
    }

    /**
     * Fungsi utama untuk menyimpan data
     */
    public function save()
    {
        $this->persist();
        $this->modal('admin-modal')->close();
    }

    /**
     * Simpan dan tetap buka modal untuk data baru (Sesuai tombol di gambar)
     */
    public function saveAndCreateAnother()
    {
        $this->persist();
        $this->resetFields();
        $this->isEdit = false;
    }

    /**
     * Logika penyimpanan database
     */
    protected function persist()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required', 
                'email', 
                Rule::unique('admins', 'email')->ignore($this->adminId)
            ],
            'email_verified_at' => 'nullable',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'password' => $this->isEdit ? 'nullable|min:8' : 'required|min:8',
            'avatar' => 'nullable|image|max:1024', // Max 1MB
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('avatars', 'public');
        }

        if ($this->isEdit) {
            Admin::findOrFail($this->adminId)->update($data);
        } else {
            // Default role jika diperlukan, sesuaikan dengan skema database kamu
            $data['role'] = $data['role'] ?? 'staff'; 
            Admin::create($data);
        }
    }

    public function toggleActive(string $id): void
    {
        $admin = Admin::findOrFail($id);
        $admin->update([
            'is_active' => ! $admin->is_active,
        ]);
    }

    public function delete(string $id): void
    {
        Admin::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.admin-index', [
            'admins' => Admin::orderByDesc('created_at')->paginate(10),
        ]);
    }
}
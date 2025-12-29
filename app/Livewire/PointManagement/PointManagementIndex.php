<?php

namespace App\Livewire\PointManagement;

use App\Models\Point;
use App\Enums\PointStatusEnum;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;

#[Layout('layouts.app')]
#[Title('Point Management')]
class PointManagementIndex extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $id, $key, $name, $abbr, $description, $value_idr, $value_idr_display, $parent_id, $value_parent, $icon, $oldIcon;
    public $status = 1;
    public $is_exchangeable = false;
    public $scope_service = []; 

    public $isEditing = false;

    protected function rules()
    {
        return [
            'key' => [
                'required',
                Rule::unique('points', 'key')->ignore($this->id),
            ],
            'name' => 'required|string|max:255',
            'abbr' => 'nullable|string|max:50',
            'value_idr' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'value_parent' => 'nullable|numeric|min:0',
            'parent_id' => 'nullable|exists:points,id',
            'status' => 'required',
            'is_exchangeable' => 'boolean',
            'scope_service' => 'required|array|min:1',
            'icon' => $this->isEditing ? 'nullable|image|max:1024' : 'required|image|max:1024',
        ];
    }

    public function create()
    {
        $this->resetFields();
        $this->isEditing = false;
        Flux::modal('point-modal')->show();
    }

    public function edit($id)
    {
        $this->resetFields();
        $point = Point::findOrFail($id);
        
        $this->id = $point->id;
        $this->key = $point->key;
        $this->name = $point->name;
        $this->abbr = $point->abbr;
        $this->value_idr = $point->value_idr;
        $this->value_parent = $point->value_parent;
        $this->value_idr_display = $point->value_idr ? number_format($point->value_idr, 0, ',', '.') : '';
        $this->description = $point->description;
        $this->parent_id = $point->parent_id;
        $this->status = $point->status->value;
        $this->is_exchangeable = $point->is_exchangeable;
        $this->scope_service = $point->scope_service ?? [];
        $this->oldIcon = $point->icon;
        
        $this->isEditing = true;
        Flux::modal('point-modal')->show();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'key' => $this->key,
            'name' => $this->name,
            'abbr' => $this->abbr,
            'description' => $this->description,
            'value_parent' => $this->value_parent,
            'value_idr' => $this->value_idr,
            'parent_id' => $this->parent_id,
            'status' => $this->status,
            'is_exchangeable' => $this->is_exchangeable,
            'scope_service' => $this->scope_service,
        ];

        if ($this->icon) {
            // Hapus icon lama jika ada
            $disk = env('FILESYSTEM_DISK', 'public');
            // delete old icon only if it's a storage path (not an external URL)
            if ($this->oldIcon && is_string($this->oldIcon) && !str_starts_with($this->oldIcon, ['http://', 'https://'])) {
                try {
                    Storage::disk($disk)->delete($this->oldIcon);
                } catch (\Throwable $e) {
                    // ignore deletion errors
                }
            }

            // If icon is an uploaded file, store it to configured disk and save public URL.
            if (is_string($this->icon)) {
                $data['icon'] = $this->icon;
            } else {
                $path = Storage::disk($disk)->putFile('points-icons', $this->icon);
                // try to get a publicly accessible URL for the stored file
                try {
                    $url = Storage::disk($disk)->url($path);
                } catch (\Throwable $e) {
                    // fallback to storing path
                    $url = $path;
                }
                $data['icon'] = $url;
            }
        }

        Point::updateOrCreate(['id' => $this->id], $data);

        Flux::modal('point-modal')->close();
        Flux::toast(
            text: $this->isEditing ? 'Point updated successfully.' : 'Point created successfully.',
            variant: 'success'
        );
        
        $this->resetFields();
    }

    public function removeIcon()
    {
        $disk = env('FILESYSTEM_DISK', 'public');
        // if a newly uploaded temporary file exists, just clear it
        if ($this->icon && !is_string($this->icon)) {
            $this->icon = null;
            return;
        }

        // if oldIcon is a stored path (not external url), try delete it
        if ($this->oldIcon && is_string($this->oldIcon) && !str_starts_with($this->oldIcon, ['http://', 'https://'])) {
            try {
                Storage::disk($disk)->delete($this->oldIcon);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $this->oldIcon = null;
        $this->icon = null;
    }

    public function resetFields()
    {
        $this->reset(['id', 'key', 'name', 'abbr', 'description', 'value_idr', 'value_idr_display', 'parent_id', 'value_parent', 'icon', 'oldIcon', 'is_exchangeable', 'scope_service']);
        $this->status = \App\Enums\PointStatusEnum::Active->value;
        $this->resetValidation();
    }

    public function updatedValueIdrDisplay($value)
    {
        // Remove non-digit characters, except comma and dot
        $clean = preg_replace('/[^0-9]/', '', $value);
        $this->value_idr = $clean === '' ? 0 : (int) $clean;
        // keep display formatted with thousands separator
        $this->value_idr_display = $clean === '' ? '' : number_format((int)$clean, 0, ',', '.');
    }

    public function render()
    {
        return view('livewire.point-management.point-management-index', [
            'points' => Point::with('parent')
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('key', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
            'parentPoints' => Point::whereNull('parent_id')->get(),
        ]);
    }
}

<?php

namespace App\Livewire\PointManagement;

use App\Models\Point;
use App\Enums\PointStatusEnum;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Services\FileUploadService;

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

    private function uploadToApi($file)
    {
        $uploader = app(FileUploadService::class);
        return $uploader->upload($file);
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

        DB::transaction(function () {
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
                $data['icon'] = $this->uploadToApi($this->icon);
            }

            Point::updateOrCreate(
                ['id' => $this->id],
                $data
            );
        });

        Flux::modal('point-modal')->close();
        Flux::toast(
            text: $this->isEditing
                ? 'Point updated successfully.'
                : 'Point created successfully.',
            variant: 'success'
        );

        $this->resetFields();
    }

    public function removeIcon()
    {
        $this->icon = null;
        $this->oldIcon = null;
    }


    public function resetFields()
    {
        $this->reset(['id', 'key', 'name', 'abbr', 'description', 'value_idr', 'value_idr_display', 'parent_id', 'value_parent', 'icon', 'oldIcon', 'is_exchangeable', 'scope_service']);
        $this->status = \App\Enums\PointStatusEnum::Active->value;
        $this->resetValidation();
    }

    public function updatedValueIdrDisplay($value)
    {
        $clean = preg_replace('/[^0-9]/', '', $value);
        $this->value_idr = $clean === '' ? 0 : (int) $clean;
        $this->value_idr_display = $clean === '' ? '' : number_format((int)$clean, 0, ',', '.');
    }

    public function render()
    {
        $query = Point::with('parent')
            ->select('id', 'icon', 'key', 'name', 'abbr', 'value_idr', 'parent_id', 'status', 'is_exchangeable', 'scope_service', 'created_at');

        $search = trim($this->search ?? '');

        if ($search !== '') {
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                $query->where('name', 'ilike', "%{$search}%")
                    ->orWhere('key', 'ilike', "%{$search}%");
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('key', 'like', "%{$search}%");
                });
            }
        }

        $points = $query->orderBy('created_at', 'desc')->Paginate(10);

        $parentPoints = Point::whereNull('parent_id')->select('id', 'name')->get();

        return view('livewire.point-management.point-management-index', compact('points', 'parentPoints'));
    }
}

<?php

namespace App\Livewire\ProductCategory;

use App\Models\ProductCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads; 
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Product Categories')]
class ProductCategoryIndex extends Component
{
    use WithPagination, WithFileUploads; 

    public $search = '';
    public $name = '';
    public $description = '';
    public $image; 
    public $editingCategoryId = null;
    public $oldImage = null; 

    protected $queryString = ['search' => ['except' => '']];

    public function save()
    {
        $this->validate([
            'name' => [
                'required',
                'min:3',
                'unique:product_categories,name,' . ($this->editingCategoryId ?: 'NULL') . ',id',
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:1024',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
        ];

        if ($this->image) {
            if ($this->oldImage) {
                Storage::disk('public')->delete($this->oldImage);
            }
            $data['image'] = $this->image->store('categories', 'public');
        }

        if ($this->editingCategoryId) {
            ProductCategory::find($this->editingCategoryId)->update($data);
        } else {
            ProductCategory::create($data);
        }

        $this->resetForm();
        $this->modal('category-modal')->close();
    }

    public function edit($id)
    {
        $category = ProductCategory::findOrFail($id);
        $this->editingCategoryId = $id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->oldImage = $category->image; 

        $this->modal('category-modal')->show();
    }

    public function delete($id)
    {
        $category = ProductCategory::findOrFail($id);
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'image', 'editingCategoryId', 'oldImage']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.productcategory.category-index', [
            'categories' => ProductCategory::where('name', 'ilike', '%'.$this->search.'%')
                ->latest()
                ->paginate(10),
        ]);
    }
}
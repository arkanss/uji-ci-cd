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
    public $categoryIdBeingDeleted = null; 

    protected $queryString = ['search' => ['except' => '']];

    public function create()
    {
        $this->resetForm();
        $this->modal('category-modal')->show();
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'image', 'editingCategoryId', 'oldImage', 'categoryIdBeingDeleted']);
        $this->resetValidation();
    }

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
        $this->resetForm();
        $category = ProductCategory::findOrFail($id);
        $this->editingCategoryId = $id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->oldImage = $category->image; 

        $this->modal('category-modal')->show();
    }

    public function confirmDelete($id)
    {
        $this->categoryIdBeingDeleted = $id;
        $this->modal('delete-category-modal')->show();
    }

    public function delete()
    {
        if ($this->categoryIdBeingDeleted) {
            $category = ProductCategory::findOrFail($this->categoryIdBeingDeleted);
            
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            
            $category->delete();
            $this->modal('delete-category-modal')->close();
            $this->categoryIdBeingDeleted = null;
        }
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
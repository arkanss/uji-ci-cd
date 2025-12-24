<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Livewire\Forms\Product\ProductForm;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Products')]
class ProductIndex extends Component
{
    use WithPagination, WithFileUploads;

    public ProductForm $form;
    
    public $search = '';
    public $isEdit = false;
    public $selectedProduct = null;

    public function mount()
    {
        $this->form = new ProductForm($this, 'form');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->form->reset(); 
        $this->isEdit = false;
        $this->dispatch('modal-show', name: 'product-modal');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->form->setProduct($product);
        $this->isEdit = true;
        $this->dispatch('modal-show', name: 'product-modal');
    }

    public function showDetail($id)
    {
        $this->selectedProduct = Product::with('category')->find($id);
        
        if ($this->selectedProduct) {
            $this->dispatch('modal-show', name: 'detail-modal');
        }
    }

    public function save()
    {
        if ($this->isEdit) {
            $this->form->update();
            $this->dispatch('toast', variant: 'success', text: 'Product updated successfully.');
        } else {
            $this->form->store();
            $this->dispatch('toast', variant: 'success', text: 'Product created successfully.');
        }

        $this->dispatch('modal-close', name: 'product-modal');
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        $this->dispatch('toast', variant: 'danger', text: 'Product deleted.');
    }

    public function render()
    {
        $query = Product::with(['category'])
            ->where(function ($q) {
                $searchTerm = '%' . trim($this->search) . '%';
                $q->where('name', 'ilike', $searchTerm)
                  ->orWhere('sku', 'ilike', $searchTerm)
                  ->orWhere('brand', 'ilike', $searchTerm)
                  ->orWhere('code', 'ilike', $searchTerm);
            });

        return view('livewire.product.product-index', [
            'products' => $query->latest()->paginate(10),
            'categories' => ProductCategory::orderBy('name')->get()
        ]);
    }
}
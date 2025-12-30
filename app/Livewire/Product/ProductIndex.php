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
use Illuminate\Support\Facades\DB;

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
        $query = Product::select('id', 'name', 'price', 'category_id', 'code', 'sku', 'stock', 'brand', 'created_at')
            ->with(['category' => function ($q) { $q->select('id', 'name'); }]);

        $search = trim($this->search ?? '');
        if ($search !== '') {
            $driver = DB::getDriverName();
            $term = "%{$search}%";
            if ($driver === 'pgsql') {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'ilike', $term)
                      ->orWhere('sku', 'ilike', $term)
                      ->orWhere('brand', 'ilike', $term)
                      ->orWhere('code', 'ilike', $term);
                });
            } else {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                      ->orWhere('sku', 'like', $term)
                      ->orWhere('brand', 'like', $term)
                      ->orWhere('code', 'like', $term);
                });
            }
        }

        $products = $query->orderBy('created_at', 'desc')->simplePaginate(10);
        $categories = ProductCategory::select('id', 'name')->orderBy('name')->get();

        return view('livewire.product.product-index', compact('products', 'categories'));
    }
}
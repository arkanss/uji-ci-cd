<?php

namespace App\Livewire\ProductDistribution;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\MerchantProduct;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Models\ProductStockHistory;

#[Layout('layouts.app')]
#[Title('Product Distribution')]
class ProductDistributionIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $items = []; 
    public $merchant_id, $product_id, $stock, $status, $editingId;
    public $selectedItem = null;

    protected $queryString = ['search' => ['except' => '']];

    public function mount()
    {
        $this->resetForm();
    }

    public function create()
    {
        $this->resetForm();
        $this->addDistribution(); 
        $this->modal('distribution-modal')->show();
    }

    public function addDistribution()
    {
        $this->items[] = [
            'merchant_id' => null,
            'product_id' => null,
            'stock' => 0,
            'status' => 1
        ];
    }

    public function removeDistribution($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->editingId = $id;
        $item = MerchantProduct::findOrFail($id);
        
        $this->merchant_id = $item->merchant_id;
        $this->product_id = $item->product_id;
        $this->stock = $item->stock;
        $this->status = $item->status;

        $this->modal('edit-modal')->show();
    }

    public function saveBulk()
    {
        $this->validate([
            'items.*.merchant_id' => 'required',
            'items.*.product_id' => 'required',
            'items.*.stock' => 'required|integer|min:1', 
            'items.*.status' => 'required',
        ], [
            'items.*.merchant_id.required' => 'Merchant harus dipilih',
            'items.*.product_id.required' => 'Produk harus dipilih',
            'items.*.stock.min' => 'Jumlah stok harus lebih dari 0',
        ]);

        DB::transaction(function () {
            foreach ($this->items as $index => $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($item['stock'] > $product->stock) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                }

                $product->decrement('stock', $item['stock']);

                MerchantProduct::create([
                    'merchant_id' => $item['merchant_id'],
                    'product_id' => $item['product_id'],
                    'stock' => $item['stock'],
                    'total_stock' => $item['stock'],
                    'status' => $item['status'],
                ]);

                ProductStockHistory::create([
                    'merchant_id' => $item['merchant_id'],
                    'product_id' => $item['product_id'],
                    'stock' => $item['stock'],
                    'stock_before' => 0, 
                    'stock_after' => $item['stock'],
                ]);
            }
        });

        $this->modal('distribution-modal')->close();
        $this->resetForm();
    }

    public function saveEdit()
    {
        $this->validate([
            'merchant_id' => 'required',
            'product_id' => 'required',
            'stock' => 'required|integer|min:0',
            'status' => 'required',
        ]);

        DB::transaction(function () {
            $mp = MerchantProduct::findOrFail($this->editingId);
            
            $oldStock = $mp->stock;
            $newStock = $this->stock;

            if ($oldStock != $newStock) {
                ProductStockHistory::create([
                    'merchant_id' => $this->merchant_id,
                    'product_id' => $this->product_id,
                    'stock' => $newStock - $oldStock, 
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                ]);
            }

            $mp->update([
                'merchant_id' => $this->merchant_id,
                'product_id' => $this->product_id,
                'stock' => $this->stock,
                'status' => $this->status,
            ]);
        });

        $this->modal('edit-modal')->close();
        $this->resetForm();
    }

    public function showDetail($id)
    {
        $this->selectedItem = MerchantProduct::with(['merchant', 'product'])->findOrFail($id);
        $this->modal('detail-modal')->show();
    }

    private function resetForm()
    {
        $this->items = [];
        $this->editingId = null;
        $this->merchant_id = '';
        $this->product_id = '';
        $this->stock = 0;
        $this->status = 1;
        $this->resetErrorBag();
    }

    public function render()
    {
        $distributions = MerchantProduct::with(['merchant', 'product'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('merchant', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
            })
            ->latest()
            ->paginate(10);

        return view('livewire.product-distribution.product-distribution-index', [
            'distributions' => $distributions,
            'merchants' => Merchant::all(),
            'products' => Product::all(),
        ]);
    }
}
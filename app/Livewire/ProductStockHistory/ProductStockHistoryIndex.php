<?php

namespace App\Livewire\ProductStockHistory;

use App\Models\ProductStockHistory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Product Stock History')]
class ProductStockHistoryIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedHistory = null;

    // Untuk sinkronisasi search di URL
    protected $queryString = ['search' => ['except' => '']];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function showDetail($id)
    {
        $this->selectedHistory = ProductStockHistory::with(['product', 'merchant'])->find($id);
        
        $this->modal('stock-history-detail')->show();
    }

    public function render()
    {
        $histories = ProductStockHistory::with(['product', 'merchant'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.product-stock-history.product-stock-history-index', [
            'histories' => $histories
        ]);
    }
}
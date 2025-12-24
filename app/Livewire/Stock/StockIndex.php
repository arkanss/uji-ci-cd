<?php

namespace App\Livewire\Stock;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductStockOverview;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Stock Management')]
class StockIndex extends Component
{
    use WithPagination;

    public $selectedStockId;
    protected $paginationTheme = 'tailwind';

    public function showDetail(string $id)
    {
        $this->selectedStockId = $id;
        $this->modal('stock-detail-modal')->show();
    }

    public function render()
    {
        $stocks = ProductStockOverview::with(['product'])
            ->paginate(10);

        $selectedStock = $this->selectedStockId 
            ? ProductStockOverview::with('product')->find($this->selectedStockId)
            : null;

        return view('livewire.stock.stock-index', [
            'stocks' => $stocks,
            'selectedStock' => $selectedStock,
        ]);
    }
}
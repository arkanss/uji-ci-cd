<?php

namespace App\Livewire\Stock;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductStockOverview;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Pagination\LengthAwarePaginator;

#[Layout('layouts.app')]
#[Title('Stock Management')]
class StockIndex extends Component
{
    use WithPagination;

    public $selectedStockId;
    public $selectedStock;

    protected $paginationTheme = 'tailwind';
    protected $updatesQueryString = ['selectedStockId'];

    public function showDetail(string $stockOverviewId)
    {
        $this->selectedStockId = $stockOverviewId;
        $this->selectedStock = ProductStockOverview::with('product')->findOrFail($stockOverviewId);

        $this->resetPage('stockLogsPage');
        $this->modal('stock-detail-modal')->show();
    }

public function getStockLogsProperty()
{
    if (!$this->selectedStock) {
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5);
    }

    return $this->selectedStock->product
        ->stockHistories()
        ->latest()
        ->paginate(5, ['*'], 'stockLogsPage');
}

    public function render()
    {
        $stocks = ProductStockOverview::with('product')->paginate(10);

        return view('livewire.stock.stock-index', [
            'stocks' => $stocks,
        ]);
    }
}

<?php

namespace App\Livewire\ProductStockHistory;

use App\Models\ProductStockHistory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
#[Title('Product Stock History')]
class ProductStockHistoryIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedHistory = null;

    protected $queryString = ['search' => ['except' => '']];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function showDetail($id)
    {
        $this->selectedHistory = ProductStockHistory::with([
            'product' => fn($q) => $q->select('id', 'name'),
            'merchant' => fn($q) => $q->select('user_id', 'name', 'profile_picture'),
        ])->find($id);

        $this->modal('stock-history-detail')->show();
    }

    public function render()
    {
        $search = trim($this->search ?? '');

        $query = ProductStockHistory::select('id', 'product_id', 'merchant_id', 'stock', 'stock_before', 'stock_after', 'created_at')
            ->with([
                'product' => fn($q) => $q->select('id', 'name'),
                'merchant' => fn($q) => $q->select('user_id', 'name'),
            ])
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $driver = DB::getDriverName();
            $term = "%{$search}%";
            if ($driver === 'pgsql') {
                $query->where(function ($q) use ($term) {
                    $q->whereHas('product', fn($q2) => $q2->where('name', 'ilike', $term))
                      ->orWhere('id', 'ilike', $term);
                });
            } else {
                $query->where(function ($q) use ($term) {
                    $q->whereHas('product', fn($q2) => $q2->where('name', 'like', $term))
                      ->orWhere('id', 'like', $term);
                });
            }
        }

        $histories = $query->Paginate(10);

        return view('livewire.product-stock-history.product-stock-history-index', compact('histories'));
    }
}
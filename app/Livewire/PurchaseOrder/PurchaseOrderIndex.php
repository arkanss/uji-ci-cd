<?php

namespace App\Livewire\PurchaseOrder;

use App\Models\ProductDistribution; 
use App\Enums\OrderRequestEnum;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
#[Title('Purchase Order Requests')]
class PurchaseOrderIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedOrder = null;
    public $rejectReason = '';

    protected $queryString = ['search' => ['except' => '']];

    public function showDetail($id)
    {
        $this->selectedOrder = null;

        $order = ProductDistribution::with([
            'items.product', 
            'items.unit', 
            'requester',
            'verifier'
        ])->findOrFail($id);
        
        $this->selectedOrder = $order;
        
        $this->modal('detail-modal')->show();
    }

    public function verifyOrder($id)
    {
        $order = ProductDistribution::with('items')->findOrFail($id);
        
        DB::transaction(function () use ($order) {
            $order->update([
                'status' => OrderRequestEnum::Verified,
                'verified_by' => Auth::id(),
            ]);

            foreach ($order->items as $item) {
                $item->update([
                    'approved_stock' => $item->requested_stock
                ]);
            }
        });

        $this->modal('detail-modal')->close();
    }

    public function rejectOrder($id)
    {
        $this->validate([
            'rejectReason' => 'required|min:5',
        ]);

        $order = ProductDistribution::findOrFail($id);
        $order->update([
            'status' => OrderRequestEnum::Rejected,
            'verified_by' => Auth::id(),
            'reject_reason' => $this->rejectReason
        ]);

        $this->rejectReason = '';
        $this->modal('detail-modal')->close();
        $this->modal('reject-modal')->close(); 
    }

    public function render()
    {
        $orders = ProductDistribution::query()
            ->where('code', 'like', '%' . $this->search . '%')
            ->orderByRaw("
                CASE 
                    WHEN status = 1 THEN 1 -- Requested
                    WHEN status = 2 THEN 2 -- Verified
                    WHEN status = 3 THEN 3 -- Processing
                    WHEN status = 4 THEN 4 -- Processed
                    WHEN status = 5 THEN 5 -- Delivering
                    WHEN status = 6 THEN 6 -- Delivered
                    WHEN status = 7 THEN 7 -- Completed
                    WHEN status = 8 THEN 8 -- Rejected
                    ELSE 9
                END ASC
            ")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.purchase-order.purchase-order-index', [
            'orders' => $orders,
        ]);
    }
}
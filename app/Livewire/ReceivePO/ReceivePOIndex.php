<?php

namespace App\Livewire\ReceivePO;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Flux\Flux;
use App\Models\ProductStockOverview;
use App\Models\ProductStockHistory;

#[Layout('layouts.app')]
#[Title('Receive Purchase Order')]
class ReceivePOIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedPO = null;
    public $receivingId = null;
    public $viewOnly = false;
    public $receiveItems = []; 

    public function render()
    {
        $query = PurchaseOrder::select('id', 'po_number', 'created_at', 'status', 'completed_by', 'completed_at', 'warehouse_id')
            ->with('completedBy:id,name')
            ->whereIn('status', [1, 2])
            ->orderBy('status')
            ->orderBy('created_at', 'desc');

        $search = trim($this->search ?? '');
        if ($search !== '') {
            $query->where('po_number', 'like', "%{$search}%");
        }

        $purchaseOrders = $query->paginate(10);

        return view('livewire.receive-po.receive-po-index', [
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    public function showReceive($id)
    {
        $po = PurchaseOrder::with(['items.product', 'items.unit'])->findOrFail($id);
        $this->receivingId = $po->id;
        $this->viewOnly = false;
        $this->selectedPO = $po;
        $this->receiveItems = $po->items->map(function ($it) {
            return [
                'id' => $it->id,
                'product_id' => $it->product_id,
                'product_name' => $it->product?->name,
                'requested_stock' => $it->requested_stock,
                'received_stock' => $it->received_stock ?? $it->requested_stock,
                'unit_name' => $it->unit?->name,
            ];
        })->toArray();

        Flux::modal('receive-po-modal')->show();
    }

    public function showDetail($id)
    {
        $po = PurchaseOrder::with(['items.product', 'items.unit'])->findOrFail($id);
        $this->receivingId = null;
        $this->viewOnly = true;
        $this->selectedPO = $po;
        $this->receiveItems = $po->items->map(function ($it) {
            return [
                'id' => $it->id,
                'product_id' => $it->product_id,
                'product_name' => $it->product?->name,
                'requested_stock' => $it->requested_stock,
                'received_stock' => $it->received_stock ?? 0,
                'unit_name' => $it->unit?->name,
            ];
        })->toArray();

        Flux::modal('receive-po-modal')->show();
    }

    public function saveReceive()
    {
        if (! $this->receivingId) {
            session()->flash('po_error', 'No PO selected');
            return;
        }

        $this->validate([
            'receiveItems' => 'required|array|min:1',
            'receiveItems.*.received_stock' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () {
        $po = PurchaseOrder::lockForUpdate()->findOrFail($this->receivingId);

        if ($po->status !== 1) {
            throw new \Exception('PO already completed');
        }

        foreach ($this->receiveItems as $it) {
            $poi = PurchaseOrderItem::lockForUpdate()->find($it['id']);
            if (! $poi) continue;

            $received = (int) $it['received_stock'];

            $poi->received_stock = $received;
            $poi->save();

            $overview = ProductStockOverview::where('products_id', $poi->product_id)
                ->lockForUpdate()
                ->first();

            if ($overview) {
                $beforeStock = $overview->stock_available;
                $overview->stock_available += $received;
                $overview->save();
            } else {
                $beforeStock = 0;
                $overview = ProductStockOverview::create([
                    'products_id' => $poi->product_id,
                    'stock_available' => $received,
                    'stock_in_delivery' => 0,
                    'bad_stock' => 0,
                ]);
            }

            ProductStockHistory::create([
                'product_id' => $poi->product_id,
                'stock' => $received, 
                'stock_before' => $beforeStock,
                'stock_after' => $overview->stock_available,
                'status' => 'received', 
            ]);
        }

        $po->status = 2;
        $po->completed_by = Auth::id();
        $po->completed_at = Carbon::now();
        $po->save();
    });

        session()->flash('po_success', 'PO received and completed');
        $this->receivingId = null;
        $this->receiveItems = [];
        Flux::modal('receive-po-modal')->close();
    }
}

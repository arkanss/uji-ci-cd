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

#[Layout('layouts.app')]
#[Title('Receive Purchase Order')]
class ReceivePOIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedPO = null;
    public $receivingId = null;
    public $viewOnly = false;
    public $receiveItems = []; // id, product_name, requested_stock, received_stock, unit_name

    public function render()
    {
        $purchaseOrders = PurchaseOrder::with('completedBy')
            ->where('po_number', 'like', '%' . $this->search . '%')
            ->whereIn('status', [1, 2]) // include Requested and Completed
            ->orderBy('status') // put Requested (1) before Completed (2)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

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
            $po = PurchaseOrder::findOrFail($this->receivingId);
            foreach ($this->receiveItems as $it) {
                $poi = PurchaseOrderItem::find($it['id']);
                if (! $poi) continue;
                $received = intval($it['received_stock']);
                $poi->received_stock = $received;
                $poi->save();

                $overview = \App\Models\ProductStockOverview::where('products_id', $poi->product_id)->first();
                if ($overview) {
                    $overview->stock_available = (int) $overview->stock_available + $received;
                    $overview->save();
                } else {
                    \App\Models\ProductStockOverview::create([
                        'products_id' => $poi->product_id,
                        'stock_available' => $received,
                        'stock_in_delivery' => 0,
                        'bad_stock' => 0,
                    ]);
                }
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

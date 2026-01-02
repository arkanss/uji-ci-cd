<?php

namespace App\Livewire\Finance\PurchaseOrder;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Flux\Flux;

#[Title('Purchase Order')]
class PurchaseOrderIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $confirmingDeleteId = null;
    public $selectedPO = null;

    public function confirmDelete($id)
    {
        $this->confirmingDeleteId = $id;
        Flux::modal('delete-po-modal')->show();
    }

    public function deleteConfirmed()
    {
        if ($this->confirmingDeleteId) {
            $this->delete($this->confirmingDeleteId);
            $this->confirmingDeleteId = null;
            Flux::modal('delete-po-modal')->close();
        }
    }

    public function delete($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status == 2) {
            session()->flash('po_error', 'Cannot delete completed PO');
            return;
        }

        DB::transaction(function () use ($po) {
            PurchaseOrderItem::where('purchase_order_id', $po->id)->delete();
            $po->delete();
        });

        session()->flash('po_success', 'Purchase Order deleted');
    }

    public function showDetail($id)
    {
        $po = PurchaseOrder::with(['items.product', 'items.unit', 'createdBy', 'completedBy', 'vendor'])->findOrFail($id);

        $items = $po->items->map(function ($it) {
            return (object) [
                'product_name' => $it->product?->name,
                'item_description' => $it->item_description,
                'requested_stock' => $it->requested_stock,
                'received_stock' => $it->received_stock,
                'unit_name' => $it->unit?->name,
                'unit_price' => $it->unit_price,
                'discount_percent' => $it->discount_percent,
                'tax_amount' => $it->tax_amount,
                'line_total' => $it->line_total,
            ];
        });

        $warehouse = DB::table('warehouse_addresses')->where('id', $po->warehouse_id)->value('name');

        $this->selectedPO = [
            'po' => $po,
            'items' => $items,
            'warehouse' => $warehouse,
            'vendorName' => $po->vendor?->name,
            'createdBy' => $po->createdBy?->name,
            'completedBy' => $po->completedBy?->name,
        ];

        Flux::modal('detail-po-modal')->show();
    }

    public function render()
    {
        $purchaseOrders = PurchaseOrder::select('id', 'po_number', 'status', 'created_by', 'created_at', 'vendor_id', 'grand_total', 'currency')
            ->with('vendor:id,name')
            ->where(function ($query) {
                $query->where('po_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('vendor', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $userIds = $purchaseOrders->pluck('created_by')->filter()->unique()->toArray();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        return view('livewire.finance.purchase-order.index', [
            'purchaseOrders' => $purchaseOrders,
            'users' => $users,
        ])->layout('layouts.app', [
            'breadcrumbs' => breadcrumbs(
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Finance', 'url' => '#'],
                'Purchase Orders'
            ),
        ]);
    }
}

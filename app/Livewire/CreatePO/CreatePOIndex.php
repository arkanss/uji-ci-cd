<?php

namespace App\Livewire\CreatePO;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Flux\Flux;

#[Layout('layouts.app')]
#[Title('Create Purchase Order')]
class CreatePOIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedId;
    public $po_number, $date, $warehouse_id, $status, $notes;
    public $items = []; // repeater: each item ['product_id','requested_stock','unit_id']
    public $isEditing = false;
    public $confirmingDeleteId = null;
    public $selectedPO = null;

    public function mount()
    {
        $this->date = now()->format('Y-m-d H:i:s');
    }

    public function create()
    {
        $this->resetForm();
        $this->date = now()->format('Y-m-d H:i:s');
        $this->isEditing = false;

        Flux::modal('po-modal')->show();
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => null, 'requested_stock' => 0, 'unit_id' => null];
    }

    public function removeItem($index)
    {
        if (isset($this->items[$index])) {
            array_splice($this->items, $index, 1);
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

    public function showDetail($id)
    {
        $po = PurchaseOrder::with(['items.product', 'items.unit', 'createdBy', 'completedBy'])->findOrFail($id);

        // map items to simple objects for the view
        $items = $po->items->map(function ($it) {
            return (object) [
                'product_name' => $it->product?->name,
                'requested_stock' => $it->requested_stock,
                'received_stock' => $it->received_stock,
                'unit_name' => $it->unit?->name,
            ];
        });

        $warehouse = DB::table('warehouse_addresses')->where('id', $po->warehouse_id)->value('name');

        $this->selectedPO = [
            'po' => $po,
            'items' => $items,
            'warehouse' => $warehouse,
            'createdBy' => $po->createdBy?->name,
            'completedBy' => $po->completedBy?->name,
        ];

        Flux::modal('detail-po-modal')->show();
    }

    public function edit($id)
    {
        $this->resetForm();
        $po = PurchaseOrder::findOrFail($id);
        $this->selectedId = $id;
        $this->po_number = $po->po_number;
        $this->warehouse_id = $po->warehouse_id;
        $this->status = $po->status;
        $this->notes = $po->notes;
        $this->date = $po->date->format('Y-m-d H:i:s');
        $this->isEditing = true;

        // load items
        $this->items = PurchaseOrderItem::where('purchase_order_id', $po->id)
            ->get(['product_id','requested_stock','unit_id'])
            ->map(fn($it)=>[
                'product_id'=>$it->product_id,
                'requested_stock'=>$it->requested_stock,
                'unit_id'=>$it->unit_id,
            ])->toArray();

        Flux::modal('po-modal')->show();
    }

    public function save()
    {
        // basic validation for header fields
        $this->validate([
            'po_number' => 'required',
            'date' => 'required|date',
            'warehouse_id' => 'required',
            'items' => 'array',
        ]);

        // ensure at least one valid item (product_id present)
        $validItems = array_values(array_filter($this->items, fn($it) => !empty($it['product_id'])));
        if (count($validItems) === 0) {
            session()->flash('po_error', 'Please add at least one item before saving.');
            return;
        }

        // validate each item
        $this->validate([
            'items.*.product_id' => 'required',
            'items.*.requested_stock' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($validItems) {
            $poData = [
                'po_number' => $this->po_number,
                'date' => \Carbon\Carbon::parse($this->date),
                'warehouse_id' => $this->warehouse_id,
                'status' => $this->status ?? 1, // 1 = Requested
                'created_by' => Auth::id(),
            ];

            $po = PurchaseOrder::updateOrCreate(['id' => $this->selectedId], $poData);

            // sync items: remove old then insert new (only valid items)
            PurchaseOrderItem::where('purchase_order_id', $po->id)->delete();

            foreach ($validItems as $it) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $it['product_id'],
                    'requested_stock' => $it['requested_stock'],
                    'unit_id' => $it['unit_id'] ?? null,
                    'received_stock' => null,
                ]);
            }
        });

        session()->flash('po_success', 'Purchase Order saved');
        $this->resetForm();
        Flux::modal('po-modal')->close();
    }

    public function resetForm()
    {
        $this->reset(['po_number','warehouse_id','selectedId','isEditing','status','notes','items']);
        $this->date = now()->format('Y-m-d H:i:s');
        $this->resetValidation();
    }
    
    public function render()
    {
        $purchaseOrders = PurchaseOrder::select('id','po_number','status','created_by','created_at')
            ->where('po_number', 'like', '%' . $this->search . '%')
            ->orderBy('created_at','desc')
            ->paginate(10);

        // eager load createdBy names manually
        $userIds = $purchaseOrders->pluck('created_by')->filter()->unique()->toArray();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        // load dropdown data
        $warehouses = DB::table('warehouse_addresses')->select('id','name')->get();
        $products = Product::select('id','name')->orderBy('name')->get();
        $units = Unit::whereIn('name', ['Box','Carton','Dozen','Pack','Pieces'])->get();

        return view('livewire.create-p-o.create-p-o-index', [
            'purchaseOrders' => $purchaseOrders,
            'users' => $users,
            'warehouses' => $warehouses,
            'products' => $products,
            'units' => $units,
        ]);
    }
}
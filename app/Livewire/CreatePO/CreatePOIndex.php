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
    public $po_number, $date, $warehouse_id, $status;
    public $items = [];
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
        $this->date = $po->date->format('Y-m-d H:i:s');
        $this->isEditing = true;

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
        $this->validate([
            'date' => 'required|date',
            'warehouse_id' => 'required',
            'items' => 'array',
        ]);

        $validItems = array_values(array_filter($this->items, fn($it) => !empty($it['product_id'])));
        if (count($validItems) === 0) {
            session()->flash('po_error', 'Please add at least one item before saving.');
            return;
        }

        $this->validate([
            'items.*.product_id' => 'required',
            'items.*.requested_stock' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($validItems) {
            $poData = [
                'date' => \Carbon\Carbon::parse($this->date),
                'warehouse_id' => $this->warehouse_id,
                'status' => $this->status ?? 1,
                'created_by' => Auth::id(),
            ];

            if (empty($this->selectedId)) {
                $datePart = Carbon::parse($this->date)->format('Ymd');
                $last = PurchaseOrder::whereDate('created_at', Carbon::parse($this->date)->toDateString())
                    ->where('po_number', 'like', "PO-{$datePart}-%")
                    ->orderBy('po_number', 'desc')
                    ->value('po_number');

                $seq = 1;
                if ($last) {
                    $lastSeq = (int) substr($last, -3);
                    $seq = $lastSeq + 1;
                }
                $poNumber = 'PO-' . $datePart . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
                $poData['po_number'] = $poNumber;
                $this->po_number = $poNumber;
            }

            $po = PurchaseOrder::updateOrCreate(['id' => $this->selectedId], $poData);

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
        $this->reset(['po_number','warehouse_id','selectedId','isEditing','status','items']);
        $this->date = now()->format('Y-m-d H:i:s');
        $this->resetValidation();
    }
    
    public function render()
    {
        $purchaseOrders = PurchaseOrder::select('id','po_number','status','created_by','created_at')
            ->where('po_number', 'like', '%' . $this->search . '%')
            ->orderBy('created_at','desc')
            ->paginate(10);

        $userIds = $purchaseOrders->pluck('created_by')->filter()->unique()->toArray();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

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
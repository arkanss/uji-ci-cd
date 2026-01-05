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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Enums\PurchaseOrderStatusEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\UserSignature;

#[Title('Purchase Order')]
class PurchaseOrderIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $confirmingDeleteId = null;
    public $selectedPO = null;
    public bool $approvalMode = false;
    public ?string $rejectReason = null;

    private function terbilang($number)
    {
        $angka = [
            '', 'satu', 'dua', 'tiga', 'empat',
            'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
            'sepuluh', 'sebelas'
        ];

        if ($number < 12) {
            return $angka[$number];
        } elseif ($number < 20) {
            return $this->terbilang($number - 10) . ' belas';
        } elseif ($number < 100) {
            return $this->terbilang(intval($number / 10)) . ' puluh ' . $this->terbilang($number % 10);
        } elseif ($number < 200) {
            return 'seratus ' . $this->terbilang($number - 100);
        } elseif ($number < 1000) {
            return $this->terbilang(intval($number / 100)) . ' ratus ' . $this->terbilang($number % 100);
        } elseif ($number < 2000) {
            return 'seribu ' . $this->terbilang($number - 1000);
        } elseif ($number < 1000000) {
            return $this->terbilang(intval($number / 1000)) . ' ribu ' . $this->terbilang($number % 1000);
        } elseif ($number < 1000000000) {
            return $this->terbilang(intval($number / 1000000)) . ' juta ' . $this->terbilang($number % 1000000);
        }

        return 'angka terlalu besar';
    }

    public function download(string $id)
    {
        $po = PurchaseOrder::with([
            'items.product',
            'items.unit',
            'vendor',
            'createdBy',
            'completedBy',
        ])->findOrFail($id);

        $items = $po->items->map(function ($item) {
            return [
                'product_name' => $item->product?->name,
                'brand' => $item->product?->brand,
                'unit_qty' => $item->requested_stock,
                'carton_unit' => $item->requested_stock / 24,
                'unit_price' => $item->unit_price,
                'amount' => $item->line_total,
                'description' => $item->item_description,
            ];
        });

        $total = (int) $po->grand_total;
        $terbilang = ucfirst(trim($this->terbilang($total))) . ' rupiah';

        $user = User::with('latestSignature')->find(Auth::id());

        $signature = null;

        if ($user?->latestSignature?->image) {
            $url = $user->latestSignature->image;

            $tempPath = storage_path('app/temp/signature-' . $user->id . '.webp');

            if (!File::exists(dirname($tempPath))) {
                File::makeDirectory(dirname($tempPath), 0755, true);
            }

            if (!File::exists($tempPath)) {
                file_put_contents($tempPath, file_get_contents($url));
            }

            $signature = $tempPath;
        }

        $pdf = Pdf::loadView(
            'livewire.finance.purchase-order.pdf-purchase-order',
            [
                'po' => $po,
                'items' => $items,
                'vendor' => $po->vendor,
                'terbilang' => $terbilang,
                'signature' => $signature,
            ]
        )->setPaper('A4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'PO-' . $po->po_number . '.pdf'
        );
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

    public function approvePO()
    {
        if (!$this->selectedPO) {
            return;
        }

        $po = PurchaseOrder::findOrFail($this->selectedPO['po']->id);

        if ($po->status !== PurchaseOrderStatusEnum::Requested) {
            session()->flash('po_error', 'PO sudah diproses');
            return;
        }

        DB::transaction(function () use ($po) {
            $po->update([
                'status' => PurchaseOrderStatusEnum::Completed,
                'approved_by' => Auth::id(),
                'approval_date' => now(),
                'completed_by' => Auth::id(),
                'completed_at' => now(),
            ]);
        });

        $this->reset(['approvalMode', 'rejectReason', 'selectedPO']);

        Flux::modal('detail-po-modal')->close();

        session()->flash('po_success', 'Purchase Order berhasil di-approve');
    }

    public function rejectPO()
    {
        $this->validate([
            'rejectReason' => 'required|min:5',
        ]);

        if (!$this->selectedPO) {
            return;
        }

        $po = PurchaseOrder::findOrFail($this->selectedPO['po']->id);

        if ($po->status !== PurchaseOrderStatusEnum::Requested) {
            session()->flash('po_error', 'PO sudah diproses');
            return;
        }

        DB::transaction(function () use ($po) {
            $po->update([
                'status' => PurchaseOrderStatusEnum::Rejected,
                'approved_by' => Auth::id(),
                'approval_date' => now(),
                'internal_notes' => trim(
                    ($po->internal_notes ? $po->internal_notes . "\n\n" : '')
                    . '[REJECTED] ' . $this->rejectReason
                    . ' — ' . Auth::user()->name
                    . ' (' . now()->format('d M Y H:i') . ')'
                ),
            ]);
        });

        $this->reset(['approvalMode', 'rejectReason', 'selectedPO']);

        Flux::modal('detail-po-modal')->close();

        session()->flash('po_success', 'Purchase Order ditolak');
    }

    public function showDetail($id)
    {
        $this->approvalMode = false;
        $this->rejectReason = null;

        $po = PurchaseOrder::with(['items.product', 'items.unit', 'createdBy', 'completedBy', 'vendor'])
            ->findOrFail($id);

        $items = $po->items->map(function ($it) {
            return (object) [
                'product_name' => $it->product?->name,
                'item_description' => $it->item_description,
                'requested_stock' => $it->requested_stock,
                'received_stock' => $it->received_stock,
                'unit_name' => $it->unit?->name,
            ];
        });

        $warehouse = DB::table('warehouse_addresses')
            ->where('id', $po->warehouse_id)
            ->value('name');

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

    public function openApproval($id)
    {
        $this->showDetail($id);
        $this->approvalMode = true;
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

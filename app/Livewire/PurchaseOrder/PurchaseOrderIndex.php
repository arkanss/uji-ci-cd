<?php

namespace App\Livewire\PurchaseOrder;

use App\Models\ProductDistribution; 
use App\Models\ProductDistributionDeliver;
use App\Models\User;
use App\Enums\OrderRequestEnum;
use App\Enums\ProductDistributionDeliverEnum;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ProductDistributionPayment;
use App\Enums\ProductDistributionPaymentStatusEnum;

#[Layout('layouts.app')]
#[Title('Purchase Order Requests')]
class PurchaseOrderIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedOrder = null;
    public $rejectReason = '';
    public $selectedDriverId = null;
    public array $approvedStocks = [];
    public $selectedPayment = null;
    public $paymentRejectReason = '';
    public $verifiablePayment = null;

    public $selectedOrders = [];
    public $selectAll = false;
    public $commonStatus = null;



    protected $queryString = ['search' => ['except' => '']];

    public function showPayment(string $orderId)
    {
        $this->selectedOrder = ProductDistribution::with([
            'payments' => fn ($q) => $q->orderBy('created_at', 'desc')
        ])->findOrFail($orderId);

        $this->verifiablePayment = $this->selectedOrder
            ->payments
            ->firstWhere('status', ProductDistributionPaymentStatusEnum::WaitingVerification);

        $this->modal('payment-modal')->show();
    }

    public function approvePayment()
    {
        if (
            !$this->verifiablePayment ||
            $this->verifiablePayment->status !== ProductDistributionPaymentStatusEnum::WaitingVerification
        ) {
            abort(403);
        }

        $this->verifiablePayment->update([
            'status' => ProductDistributionPaymentStatusEnum::Paid,
        ]);

        $this->verifiablePayment = null;
        $this->modal('payment-modal')->close();
        $this->dispatch('show-toast', ['message' => 'Payment approved successfully.', 'type' => 'success']);
    }

    public function rejectPayment()
    {
        $this->validate([
            'paymentRejectReason' => 'required|min:5',
        ]);

        if (
            !$this->verifiablePayment ||
            $this->verifiablePayment->status !== ProductDistributionPaymentStatusEnum::WaitingVerification
        ) {
            abort(403);
        }

        $this->verifiablePayment->update([
            'status' => ProductDistributionPaymentStatusEnum::Rejected,
            'reject_reason' => $this->paymentRejectReason,
        ]);

        $this->paymentRejectReason = '';
        $this->dispatch('show-toast', ['message' => 'Payment has been rejected.', 'type' => 'success']);
    }

    public function showDetail($id)
    {
        $this->selectedOrder = ProductDistribution::with([
            'items.product',
            'items.unit',
            'requester',
            'verifier',
            'delivery.driver',
            'payments'
        ])->findOrFail($id);

        $this->modal('detail-modal')->show();
        foreach ($this->selectedOrder->items as $item) {
            $this->approvedStocks[$item->id] = $item->requested_stock;
        }

    }

    public function verifyOrder(string $orderId)
    {
        $order = ProductDistribution::with('items')->findOrFail($orderId);

        if ($order->status !== OrderRequestEnum::Requested) {
            abort(403);
        }

        DB::transaction(function () use ($order) {

            foreach ($order->items as $item) {
                $approved = $this->approvedStocks[$item->id] ?? 0;

                if ($approved > $item->requested_stock) {
                    throw new \Exception(
                        "Approved stock for {$item->product->name} exceeds requested stock"
                    );
                }

                $item->update([
                    'approved_stock' => $approved,
                ]);
            }

            $order->update([
                'status' => OrderRequestEnum::Verified,
                'verified_by' => Auth::id(),
            ]);
        });

        $this->approvedStocks = [];
        $this->dispatch('show-toast', ['message' => 'Order berhasil diverifikasi!', 'type' => 'success']);
        $this->selectedOrder->refresh();
        $this->modal('detail-modal')->close();
    }

    public function markAsProcessing(string $id)
    {
        $order = ProductDistribution::findOrFail($id);

        if ($order->status !== OrderRequestEnum::Verified) {
            abort(403);
        }

        $order->update([
            'status' => OrderRequestEnum::Processing,
        ]);
    }

    public function assignDriverAndProcess(string $orderId)
    {
        $this->validate([
            'selectedDriverId' => 'required|exists:users,id',
        ]);

        $order = ProductDistribution::findOrFail($orderId);

        if ($order->status !== OrderRequestEnum::Processing) {
            abort(403);
        }

        DB::transaction(function () use ($order) {

            $delivery = ProductDistributionDeliver::create([
                'code' => 'DEL-' . now()->format('YmdHis'),
                'status' => ProductDistributionDeliverEnum::InCompleted,
                'driver_id' => $this->selectedDriverId,
                'date' => now(),
            ]);

            $order->update([
                'status' => OrderRequestEnum::Processed,
                'product_distribution_delivery_id' => $delivery->id,
            ]);
        });

        $this->selectedDriverId = null;
    }

    public function markAsDelivering(string $id)
    {
        $order = ProductDistribution::findOrFail($id);

        if ($order->status !== OrderRequestEnum::Processed) {
            abort(403);
        }

        $order->update([
            'status' => OrderRequestEnum::Delivering,
        ]);
    }

    public function markAsDelivered(string $id)
    {
        $order = ProductDistribution::with('delivery')->findOrFail($id);

        if ($order->status !== OrderRequestEnum::Delivering) {
            abort(403);
        }

        DB::transaction(function () use ($order) {

            $order->update([
                'status' => OrderRequestEnum::Delivered,
            ]);

            if ($order->delivery) {
                $order->delivery->update([
                    'status' => ProductDistributionDeliverEnum::Completed,
                ]);
            }
        });
    }

    public function rejectOrder($id)
    {
        $this->validate([
            'rejectReason' => 'required|min:5',
        ]);

        $order = ProductDistribution::findOrFail($id);

        if ($order->status !== OrderRequestEnum::Requested) {
            abort(403);
        }

        $order->update([
            'status' => OrderRequestEnum::Rejected,
            'verified_by' => Auth::id(),
            'reject_reason' => $this->rejectReason,
        ]);

        $this->rejectReason = '';
        $this->dispatch('show-toast', ['message' => 'Order berhasil ditolak.', 'type' => 'success']);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedOrders = ProductDistribution::query()
                ->where('code', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedOrders = [];
        }
    }

    public function showBulkActionModal()
    {
        if (empty($this->selectedOrders)) {
            return;
        }

        $statuses = ProductDistribution::whereIn('id', $this->selectedOrders)->pluck('status')->unique();

        if ($statuses->count() > 1) {
            $this->dispatch('show-toast', [
                'message' => 'Silakan pilih pesanan dengan status yang sama untuk melakukan aksi massal.',
                'type' => 'error'
            ]);
            return;
        }

        $this->commonStatus = $statuses->first();
        $this->modal('bulk-action-modal')->show();
    }

    public function bulkVerifyOrders()
    {
        // For this action, we assume all selected items are in 'Requested' status.
        // We also need to approve all items with their requested stock.
        $orders = ProductDistribution::with('items')->whereIn('id', $this->selectedOrders)->get();

        DB::transaction(function () use ($orders) {
            foreach ($orders as $order) {
                if ($order->status !== OrderRequestEnum::Requested) continue;

                foreach ($order->items as $item) {
                    $item->update([
                        'approved_stock' => $item->requested_stock,
                    ]);
                }
                $order->update([
                    'status' => OrderRequestEnum::Verified,
                    'verified_by' => Auth::id(),
                ]);
            }
        });

        $this->dispatch('show-toast', ['message' => 'Pesanan yang dipilih telah diverifikasi.', 'type' => 'success']);
        $this->modal('bulk-action-modal')->close();
        $this->selectedOrders = [];
        $this->selectAll = false;
    }

    public function bulkMarkAsProcessing()
    {
        ProductDistribution::whereIn('id', $this->selectedOrders)
            ->where('status', OrderRequestEnum::Verified)
            ->update(['status' => OrderRequestEnum::Processing]);

        $this->dispatch('show-toast', ['message' => 'Pesanan yang dipilih telah diproses.', 'type' => 'success']);
        $this->modal('bulk-action-modal')->close();
        $this->selectedOrders = [];
        $this->selectAll = false;
    }

    public function bulkAssignDriverAndProcess()
    {
        $this->validate([
            'selectedDriverId' => 'required|exists:users,id',
        ]);

        $orders = ProductDistribution::whereIn('id', $this->selectedOrders)
            ->where('status', OrderRequestEnum::Processing)
            ->get();

        DB::transaction(function () use ($orders) {
            foreach($orders as $order) {
                $delivery = ProductDistributionDeliver::create([
                    'code' => 'DEL-' . now()->format('YmdHis') . '-' . substr(md5(uniqid()), 0, 4),
                    'status' => ProductDistributionDeliverEnum::InCompleted,
                    'driver_id' => $this->selectedDriverId,
                    'date' => now(),
                ]);

                $order->update([
                    'status' => OrderRequestEnum::Processed,
                    'product_distribution_delivery_id' => $delivery->id,
                ]);
            }
        });
        
        $this->dispatch('show-toast', ['message' => 'Driver telah ditugaskan ke pesanan yang dipilih.', 'type' => 'success']);
        $this->modal('bulk-action-modal')->close();
        $this->selectedOrders = [];
        $this->selectedDriverId = null;
        $this->selectAll = false;
    }

    public function bulkMarkAsDelivering()
    {
        ProductDistribution::whereIn('id', $this->selectedOrders)
            ->where('status', OrderRequestEnum::Processed)
            ->update(['status' => OrderRequestEnum::Delivering]);
        
        $this->dispatch('show-toast', ['message' => 'Pesanan yang dipilih telah dalam pengiriman.', 'type' => 'success']);
        $this->modal('bulk-action-modal')->close();
        $this->selectedOrders = [];
        $this->selectAll = false;
    }

    public function bulkMarkAsDelivered()
    {
        $orders = ProductDistribution::with('delivery')->whereIn('id', $this->selectedOrders)->get();

        DB::transaction(function() use ($orders) {
            foreach($orders as $order) {
                if ($order->status !== OrderRequestEnum::Delivering) continue;

                $order->update([
                    'status' => OrderRequestEnum::Delivered,
                ]);

                if ($order->delivery) {
                    $order->delivery->update([
                        'status' => ProductDistributionDeliverEnum::Completed,
                    ]);
                }
            }
        });

        $this->dispatch('show-toast', ['message' => 'Pesanan yang dipilih telah terkirim.', 'type' => 'success']);
        $this->modal('bulk-action-modal')->close();
        $this->selectedOrders = [];
        $this->selectAll = false;
    }

    public function getDriversProperty()
    {
        return User::where('role', 8)->select('id', 'name')->get();
    }

    public function render()
    {
        $orders = ProductDistribution::query()
            ->select('id', 'code', 'status', 'requested_by', 'verified_by', 'created_at')
            ->where('code', 'like', '%' . $this->search . '%')
            ->with([
                'requester:id,name',
                'verifier:id,name',
                'payments' => function ($q) {
                    $q->orderBy('created_at', 'desc');
                },
            ])
            ->orderByRaw('CASE WHEN status = 1 THEN 1 WHEN status = 2 THEN 2 WHEN status = 3 THEN 3 WHEN status = 4 THEN 4 WHEN status = 5 THEN 5 WHEN status = 6 THEN 6 WHEN status = 7 THEN 7 WHEN status = 8 THEN 8 ELSE 9 END')
            ->orderBy('created_at', 'desc')
            ->simplePaginate(10);

        return view('livewire.purchase-order.purchase-order-index', compact('orders'));
    }
}
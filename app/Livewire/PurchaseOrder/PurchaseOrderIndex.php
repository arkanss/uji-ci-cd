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
#[Title('Outlet Order Requests')]
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
    public $activeTab = 'all';

    public float $subtotal = 0;
    public float $total_discount = 0;
    public float $grand_total = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'activeTab' => ['except' => 'all'],
    ];

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function showPayment(string $orderId)
    {
        $this->selectedOrder = ProductDistribution::with([
            'payments' => fn($q) => $q->orderBy('created_at', 'desc')
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

        DB::transaction(function () {
            $this->verifiablePayment->update([
                'status' => ProductDistributionPaymentStatusEnum::Paid,
            ]);
        });

        $this->verifiablePayment = null;
        $this->modal('payment-modal')->close();
        $this->dispatch('show-toast', [
            'message' => 'Payment approved successfully.',
            'type' => 'success'
        ]);
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

        DB::transaction(function () {
            $this->verifiablePayment->update([
                'status' => ProductDistributionPaymentStatusEnum::Rejected,
                'reject_reason' => $this->paymentRejectReason,
            ]);
        });

        $this->paymentRejectReason = '';
        $this->dispatch('show-toast', [
            'message' => 'Payment has been rejected.',
            'type' => 'success'
        ]);
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

        $this->selectedDriverId = '';
        $this->modal('detail-modal')->show();
        foreach ($this->selectedOrder->items as $item) {
            $this->approvedStocks[$item->id] = $item->requested_stock;
        }
    }

    public function verifyOrder(string $orderId)
    {
        DB::transaction(function () use ($orderId) {

            $order = ProductDistribution::with('items')
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->status !== OrderRequestEnum::Requested) {
                abort(403);
            }

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
        $this->dispatch('show-toast', [
            'message' => 'Order berhasil diverifikasi!',
            'type' => 'success'
        ]);
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
        $this->dispatch('show-toast', ['message' => 'Order marked as Processing.', 'type' => 'success']);
        // refresh selected order if open and close modal
        if ($this->selectedOrder && $this->selectedOrder->id == $order->id) {
            $this->selectedOrder->refresh();
            $this->modal('detail-modal')->close();
        }
    }

    public function assignDriverAndProcess(string $orderId)
    {
        $this->validate([
            'selectedDriverId' => 'required|exists:users,id',
        ]);

        $order = ProductDistribution::findOrFail($orderId);

        if ($order->status !== OrderRequestEnum::Processing) {
            abort(403, 'Assign driver hanya boleh saat status Processing');
        }

        DB::transaction(function () use ($order) {
            $delivery = ProductDistributionDeliver::create([
                'code' => 'DEL-' . now()->format('YmdHis') . '-' . substr(md5(uniqid()), 0, 4),
                'status' => ProductDistributionDeliverEnum::Pending,
                'driver_id' => $this->selectedDriverId,
                'date' => now(),
            ]);

            $order->update([
                'status' => OrderRequestEnum::Processed,
                'product_distribution_delivery_id' => $delivery->id,
            ]);

            $delivery->update([
                'status' => ProductDistributionDeliverEnum::Pending,
            ]);
        });

        $this->selectedOrder->refresh();
        $this->selectedDriverId = null;
        $this->dispatch('show-toast', ['message' => 'Driver assigned and order processed.', 'type' => 'success']);
        if ($this->selectedOrder && $this->selectedOrder->id == $order->id) {
            $this->selectedOrder->refresh();
            $this->modal('detail-modal')->close();
        }
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
        $this->dispatch('show-toast', ['message' => 'Order marked as Delivering.', 'type' => 'success']);
        if ($this->selectedOrder && $this->selectedOrder->id == $order->id) {
            $this->selectedOrder->refresh();
            $this->modal('detail-modal')->close();
        }
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
                    'status' => ProductDistributionDeliverEnum::Delivered,
                ]);
            }
        });
        $this->dispatch('show-toast', ['message' => 'Order marked as Delivered.', 'type' => 'success']);
        if ($this->selectedOrder && $this->selectedOrder->id == $order->id) {
            $this->selectedOrder->refresh();
            $this->modal('detail-modal')->close();
        }
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
        if ($this->selectedOrder && $this->selectedOrder->id == $order->id) {
            $this->selectedOrder->refresh();
            $this->modal('detail-modal')->close();
        }
    }

    public function updatedSelectAll($value)
    {
        // Optimize: only select IDs for the current page to avoid plucking millions of rows.
        if ($value) {
            $this->selectedOrders = ProductDistribution::query()
                ->where('code', 'like', '%' . $this->search . '%')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
            $this->selectAll = true;
        } else {
            $this->selectedOrders = [];
            $this->selectAll = false;
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
        $this->selectedDriverId = '';
        $this->modal('bulk-action-modal')->show();
    }

    public function bulkVerifyOrders()
    {
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
            'selectedDriverId' => 'required|not_in:|exists:users,id',
        ]);

        $orders = ProductDistribution::whereIn('id', $this->selectedOrders)
            ->where('status', OrderRequestEnum::Processing)
            ->get();

        DB::transaction(function () use ($orders) {
            foreach ($orders as $order) {
                $delivery = ProductDistributionDeliver::create([
                    'code' => 'DEL-' . now()->format('YmdHis') . '-' . substr(md5(uniqid()), 0, 4),
                    'status' => ProductDistributionDeliverEnum::Pending,
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

        DB::transaction(function () use ($orders) {
            foreach ($orders as $order) {
                if ($order->status !== OrderRequestEnum::Delivering) continue;

                $order->update([
                    'status' => OrderRequestEnum::Delivered,
                ]);

                if ($order->delivery) {
                    $order->delivery->update([
                        'status' => ProductDistributionDeliverEnum::Delivered,
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
            ->select('id', 'code', 'status', 'order_type', 'requested_by', 'verified_by', 'created_at', 'user_id')
            ->where('code', 'like', '%' . $this->search . '%')
            ->with([
                'outlet' => function ($q) {
                    $q->select([
                        'user_id',
                        'name',
                        'code',
                    ]);
                },
                'requester:id,name',
                'verifier:id,name',
                'delivery.driver',
                'payments' => fn($q) => $q->orderBy('created_at', 'desc'),
            ]);

        match ($this->activeTab) {
            'requested' => $orders->where('status', OrderRequestEnum::Requested),

            'unpaid' => $orders->whereHas('payments', function ($q) {
                $q->whereIn('status', [
                    ProductDistributionPaymentStatusEnum::Pending,
                    ProductDistributionPaymentStatusEnum::WaitingVerification,
                ]);
            })->orWhereDoesntHave('payments'),

            'paid' => $orders->whereHas('payments', function ($q) {
                $q->where('status', ProductDistributionPaymentStatusEnum::Paid);
            }),

            'need_to_process' => $orders->where('status', OrderRequestEnum::Verified),

            'in_process' => $orders->where('status', OrderRequestEnum::Processing),

            'delivery' => $orders->whereIn('status', [
                OrderRequestEnum::Processed,
                OrderRequestEnum::Delivering,
                OrderRequestEnum::Delivered,
            ]),

            default => null,
        };

        $orders = $orders
            ->orderByRaw('CASE 
                WHEN status = 1 THEN 1 
                WHEN status = 2 THEN 2 
                WHEN status = 3 THEN 3 
                WHEN status = 4 THEN 4 
                WHEN status = 5 THEN 5 
                WHEN status = 6 THEN 6 
                WHEN status = 7 THEN 7 
                WHEN status = 8 THEN 8 
                ELSE 9 END')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.purchase-order.purchase-order-index', compact('orders'));
    }
}

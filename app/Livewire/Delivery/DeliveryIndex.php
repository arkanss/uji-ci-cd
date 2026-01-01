<?php

namespace App\Livewire\Delivery;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductDistributionDeliver;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('layouts.app')]
#[Title('Delivery Management')]
class DeliveryIndex extends Component
{
    use WithPagination;

    public $selectedDeliveryId;
    protected $queryString = [
        'distPage' => ['except' => 1, 'as' => 'p'],
    ];

    public function showDetail(string $id)
    {
        $this->selectedDeliveryId = $id;
        $this->resetPage('distPage'); 
        $this->modal('delivery-detail-modal')->show();
    }

    public function downloadPdf(string $deliveryId)
    {
        $delivery = $delivery = ProductDistributionDeliver::with([
            'driver',
            'distributions.verifier',
            'distributions.items.product',
        ])->findOrFail($deliveryId);

        $qrData = "/delivery/{$delivery->id}"; 
        $qrImage = file_get_contents(
            "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData)
        );

        $qrBase64 = 'data:image/png;base64,' . base64_encode($qrImage);

        $pdf = Pdf::loadView('livewire.delivery.delivery-note', [
            'delivery' => $delivery,
            'qrBase64' => $qrBase64,
        ])->setOptions([
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            "Delivery_{$delivery->code}.pdf"
        );
    }

    public function render()
    {
        $deliveries = ProductDistributionDeliver::with(['driver'])
            ->orderByDesc('date')
            ->paginate(10);

        $selectedDelivery = null;
        $distributions = null;

        if ($this->selectedDeliveryId) {
            $selectedDelivery = ProductDistributionDeliver::with(['driver'])
                ->find($this->selectedDeliveryId);
            
            if ($selectedDelivery) {
                $distributions = $selectedDelivery->distributions()
                    ->with(['requester', 'verifier', 'items.product']) 
                    ->paginate(3, ['*'], 'distPage');
            }
        }

        return view('livewire.delivery.delivery-index', [
            'deliveries' => $deliveries,
            'selectedDelivery' => $selectedDelivery,
            'distributions' => $distributions,
        ]);
    }
}
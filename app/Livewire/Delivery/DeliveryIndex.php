<?php

namespace App\Livewire\Delivery;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductDistributionDeliver;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Delivery Management')]
class DeliveryIndex extends Component
{
    use WithPagination;

    public $selectedDeliveryId;
    protected $paginationTheme = 'tailwind';

    public function showDetail(string $id)
    {
        $this->selectedDeliveryId = $id;
        $this->modal('delivery-detail-modal')->show();
    }

    public function render()
    {
        $deliveries = ProductDistributionDeliver::with(['driver', 'distributions'])
            ->orderByDesc('date')
            ->paginate(10);

        $selectedDelivery = $this->selectedDeliveryId 
            ? ProductDistributionDeliver::with(['driver', 'distributions.merchant', 'distributions.product'])
                ->find($this->selectedDeliveryId) 
            : null;

        return view('livewire.delivery.delivery-index', [
            'deliveries' => $deliveries,
            'selectedDelivery' => $selectedDelivery,
        ]);
    }
}
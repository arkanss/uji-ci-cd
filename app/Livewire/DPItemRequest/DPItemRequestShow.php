<?php

namespace App\Livewire\DPItemRequest;

use Livewire\Component;
use App\Models\WorkerInventoryRequests;
use App\Enums\DPItemRequestsEnum;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Detail DP Item Requests')]
class DPItemRequestShow extends Component
{
    public $requestId;
    public $request;

    public $receivedStock = [];

    public function mount($id)
    {
        $this->requestId = $id;
        $this->request = WorkerInventoryRequests::with([
            'items.product',
            'user',
            'openedVerifyBy',
            'closedVerifyBy',
        ])->findOrFail($id);

        foreach ($this->request->items as $item) {
            $this->receivedStock[$item->id] = $item->received_stock;
        }
    }

    protected function rules()
    {
        $rules = [];

        foreach ($this->request->items as $item) {
            $rules["receivedStock.{$item->id}"] = [
                'required',
                'integer',
                'min:0',
                'max:' . $item->requested_stock,
            ];
        }

        return $rules;
    }

    public function markAsOpened()
    {
        if (!in_array($this->request->status->value, [1, 2])) {
            return;
        }

        $this->validate();

        foreach ($this->request->items as $item) {
            $item->update([
                'received_stock' => $this->receivedStock[$item->id],
            ]);
        }

        $this->request->update([
            'status' => DPItemRequestsEnum::Opened,
            'opened_at' => now(),
            'opened_verified_by' => Auth::id(),
        ]);

        $this->request->refresh();
    }

    public function markAsClosed()
    {
        if ($this->request->status !== DPItemRequestsEnum::Opened) {
            return;
        }

        $this->request->update([
            'status' => DPItemRequestsEnum::Closed,
            'closed_at' => now(),
            'closed_verified_by' => Auth::id(),
        ]);

        $this->request->refresh();
    }

    public function render()
    {
        return view('livewire.d-p-item-request.dp-item-request-show');
    }
}

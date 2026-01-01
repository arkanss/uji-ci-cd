<?php

namespace App\Livewire\DPItemRequest;

use Livewire\Component;
use App\Models\WorkerInventoryRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
#[Title('DP Item Requests')]
class DPItemRequestEdit extends Component
{
    public $request;
    public $status;
    public $receivedStock = [];

    public function mount($id)
    {
        $this->request = WorkerInventoryRequests::with('items.product')
            ->findOrFail($id);

        if (!in_array($this->request->status->value, [1, 2])) {
            abort(403, 'Request cannot be edited');
        }

        $this->status = $this->request->status->value;

        foreach ($this->request->items as $item) {
            $this->receivedStock[$item->id] = $item->received_stock;
        }
    }

    protected function rules()
    {
        $rules = [
            'status' => 'required|in:1,2',
        ];

        foreach ($this->request->items as $item) {
            $rules["receivedStock.{$item->id}"] = [
                'nullable',
                'integer',
                'min:0',
                'max:' . $item->requested_stock,
            ];
        }

        return $rules;
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            foreach ($this->request->items as $item) {
                $item->update([
                    'received_stock' => $this->receivedStock[$item->id],
                ]);
            }

            $this->request->update([
                'status' => $this->status,
            ]);
        });

        session()->flash('success', 'Request updated successfully');

        return redirect()->route('dp-item-requests.show', $this->request->id);
    }

    public function render()
    {
        return view('livewire.d-p-item-request.dp-item-request-edit');
    }
}

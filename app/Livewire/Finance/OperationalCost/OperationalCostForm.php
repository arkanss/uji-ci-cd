<?php

namespace App\Livewire\Finance\OperationalCost;

use App\Models\OperationalCost;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;

#[Title('Operational Cost Form')]
class OperationalCostForm extends Component
{
    use WithFileUploads;

    public $editingId = null;
    public $items = [];

    protected $rules = [
        'items.*.title' => 'required|min:3',
        'items.*.amount' => 'required',
        'items.*.date' => 'required|date',
        // allow images and pdf up to 5MB
        'items.*.attachment' => 'nullable|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
    ];

    public function mount($id = null)
    {
        $this->editingId = $id;

        if ($id) {
            $cost = OperationalCost::findOrFail($id);
            $this->items = [[
                'title' => $cost->title,
                'amount' => number_format($cost->amount, 0, ',', '.'),
                'date' => \Carbon\Carbon::parse($cost->date)->format('Y-m-d'),
                'attachment' => $cost->attachments_url,
            ]];
        } else {
            $this->items = [[
                'title' => '',
                'amount' => '',
                'date' => now()->format('Y-m-d'),
                'attachment' => null,
            ]];
        }
    }

    public function save(FileUploadService $uploader)
    {
        // validate all items first
        // If an item's attachment is a string (existing URL), remove it from
        // the copy used for validation so the 'mimes' rule doesn't run on it.
        $itemsForValidation = $this->items;
        foreach ($itemsForValidation as $k => $it) {
            if (isset($it['attachment']) && is_string($it['attachment'])) {
                unset($itemsForValidation[$k]['attachment']);
            }
        }

        Validator::make(['items' => $itemsForValidation], $this->rules)->validate();

        DB::transaction(function () use ($uploader) {
            foreach ($this->items as $item) {
                $cleanAmount = (int) str_replace('.', '', $item['amount']);

                $data = [
                    'title' => $item['title'],
                    'amount' => $cleanAmount,
                    'date' => $item['date'],
                    'created_by' => Auth::id(),
                ];

                if (isset($item['attachment']) && $item['attachment'] && ! is_string($item['attachment'])) {
                    $data['attachments_url'] = $uploader->upload($item['attachment']);
                } elseif (isset($item['attachment']) && is_string($item['attachment'])) {
                    // keep existing URL
                    $data['attachments_url'] = $item['attachment'];
                }

                OperationalCost::create($data);
            }
        });

        session()->flash('oc_success', 'Operational cost(s) saved successfully');

        return redirect()->route('finance.operational-cost.index');
    }

    public function addItem()
    {
        $this->items[] = [
            'title' => '',
            'amount' => '',
            'date' => now()->format('Y-m-d'),
            'attachment' => null,
        ];
    }

    public function removeItem($index)
    {
        if (isset($this->items[$index])) {
            array_splice($this->items, $index, 1);
        }
        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function render()
    {
        return view('livewire.finance.operational-cost.operational-cost-form')->layout('layouts.app', [
            'breadcrumbs' => breadcrumbs(
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Operational Costs', 'url' => route('finance.operational-cost.index')],
                ['label' => $this->editingId ? 'Edit' : 'Create']
            ),
        ]);
    }
}

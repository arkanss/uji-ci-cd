<?php

namespace App\Livewire\Finance\OperationalCost;

use App\Models\OperationalCost;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Flux\Flux;
use Carbon\Carbon;

#[Title('Operational Cost')]
class OperationalCostIndex extends Component
{
    use WithPagination, WithFileUploads;

    protected $listeners = ['openEdit' => 'openEdit'];

    public $search = '';

    // modal/form state (single item form)
    public $title;
    public $amount;
    public $date;
    public $attachment;
    public $editingId = null;
    public $isEdit = false;
    public $items = [];

    protected $rules = [
        'title' => 'required|min:3',
        'amount' => 'required',
        'date' => 'required|date',
        'attachment' => 'nullable|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
    ];

    // rules for multi-item create
    protected $multiRules = [
        'items.*.title' => 'required|min:3',
        'items.*.amount' => 'required',
        'items.*.date' => 'required|date',
        'items.*.attachment' => 'nullable|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
    ];

    public function resetForm()
    {
        $this->editingId = null;
        $this->isEdit = false;
        $this->items = [[
            'title' => '',
            'amount' => '',
            'date' => Carbon::now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i'),
            'attachment' => null,
        ]];
        $this->reset(['title', 'amount', 'date', 'attachment', 'editingId', 'isEdit']);
        $this->resetErrorBag();
        // note: do NOT close modal here to avoid race with client-side modal trigger
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->date = Carbon::now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        Flux::modal('cost-modal')->show();
    }

    public function openEdit($id)
    {
        $cost = OperationalCost::findOrFail($id);
        $this->resetForm();
        $this->editingId = $id;
        $this->isEdit = true;

        $this->title = $cost->title;
        $this->amount = number_format($cost->amount, 0, ',', '.');
        $this->date = Carbon::parse($cost->date)->timezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        $this->attachment = $cost->attachments_url;

        Flux::modal('cost-modal')->show();
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

    public function delete($id)
    {
        OperationalCost::findOrFail($id)->delete();
    }

    public function save(FileUploadService $uploader)
    {
        if ($this->isEdit && $this->editingId) {
            // validate single with custom messages
            $singleMessages = [
                'title.required' => 'Title wajib di isi',
                'amount.required' => 'Amount wajib di isi',
            ];
            Validator::make([
                'title' => $this->title,
                'amount' => $this->amount,
                'date' => $this->date,
            ], [
                'title' => $this->rules['title'],
                'amount' => $this->rules['amount'],
                'date' => $this->rules['date'],
            ], $singleMessages)->validate();

            DB::transaction(function () use ($uploader) {
                $cleanAmount = (int) str_replace('.', '', $this->amount);

                $data = [
                    'title' => $this->title,
                    'amount' => $cleanAmount,
                    'date' => $this->date,
                    'created_by' => Auth::id(),
                ];

                if ($this->attachment && ! is_string($this->attachment)) {
                    $data['attachments_url'] = $uploader->upload($this->attachment);
                } elseif ($this->attachment && is_string($this->attachment)) {
                    $data['attachments_url'] = $this->attachment;
                }

                OperationalCost::findOrFail($this->editingId)->update($data);
            });

            session()->flash('oc_success', 'Operational cost updated successfully');
        } else {
            // validate multiple items
            $itemsForValidation = $this->items;
            foreach ($itemsForValidation as $k => $it) {
                if (isset($it['attachment']) && is_string($it['attachment'])) {
                    unset($itemsForValidation[$k]['attachment']);
                }
            }
            $messages = [
                'items.*.title.required' => 'Title wajib di isi',
                'items.*.amount.required' => 'Amount wajib di isi',
            ];
            $attributes = [
                'items.*.title' => 'Title',
                'items.*.amount' => 'Amount',
            ];
            Validator::make(['items' => $itemsForValidation], $this->multiRules, $messages, $attributes)->validate();

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
                        $data['attachments_url'] = $item['attachment'];
                    }

                    OperationalCost::create($data);
                }
            });

            session()->flash('oc_success', 'Operational cost(s) saved successfully');
        }

        try { Flux::modal('cost-modal')->close(); } catch (\Throwable $e) {}
        $this->resetForm();
        $this->resetPage();
    }

    public function render()
    {
        $query = OperationalCost::with('creator')
            ->select('id', 'title', 'amount', 'attachments_url', 'date', 'created_by')
            ->orderBy('date', 'desc');

        $search = trim($this->search ?? '');

        if ($search !== '') {
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                $query->where('title', 'ilike', "%{$search}%");
            } else {
                $query->where('title', 'like', "%{$search}%");
            }
        }

        $costs = $query->Paginate(10);

        return view('livewire.finance.operational-cost.operational-cost-index', compact('costs'))->layout('layouts.app', [
            'breadcrumbs' => breadcrumbs(
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Operational Costs', 'url' => '']
            ),
        ]);
    }
}

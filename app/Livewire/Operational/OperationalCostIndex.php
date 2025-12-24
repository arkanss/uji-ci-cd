<?php

namespace App\Livewire\Operational;

use App\Models\OperationalCost;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
#[Title('Operational Costs')]
class OperationalCostIndex extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $title = '';
    public $amount = 0;
    public $date;
    public $attachment; 
    public $oldAttachment; 
    public $editingId = null;

    protected $queryString = ['search' => ['except' => '']];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|min:3',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'attachment' => 'nullable|file|max:2048', 
        ]);

        /** @var \App\Models\User $user */
        $userId = Auth::id(); 

        $data = [
            'title'      => $this->title,
            'amount'     => $this->amount,
            'date'       => $this->date,
            'created_by' => $userId,
        ];

        if ($this->attachment) {
            if ($this->oldAttachment) {
                Storage::disk('public')->delete($this->oldAttachment);
            }
            $data['attachments_url'] = $this->attachment->store('operational-costs', 'public');
        }

        if ($this->editingId) {
            OperationalCost::find($this->editingId)?->update($data);
        } else {
            OperationalCost::create($data);
        }

        $this->resetForm();
        $this->modal('cost-modal')->close();
    }

    public function edit($id)
    {
        /** @var OperationalCost $cost */
        $cost = OperationalCost::findOrFail($id);

        $this->editingId = $cost->id;
        $this->title = $cost->title;
        $this->amount = $cost->amount;
        $this->date = $cost->date;
        $this->oldAttachment = $cost->attachments_url;

        $this->modal('cost-modal')->show();
    }

    public function delete($id)
    {
        $cost = OperationalCost::findOrFail($id);
        
        if ($cost->attachments_url) {
            Storage::disk('public')->delete($cost->attachments_url);
        }

        $cost->delete();
    }

    public function resetForm()
    {
        $this->reset(['title', 'amount', 'attachment', 'editingId', 'oldAttachment']);
        $this->date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.operational.operational-cost-index', [
            'costs' => OperationalCost::with('creator')
                ->where('title', 'ilike', '%' . $this->search . '%')
                ->latest('date')
                ->paginate(10),
        ]);
    }
}
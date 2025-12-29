<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\ProductReward;
use App\Models\RewardableEntity;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Product Rewards')]
class ProductRewardsIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $rewards = ProductReward::with(['product:id,name', 'rewardableEntity:id,type'])
            ->latest()
            ->paginate(10);

        $products = cache()->remember('products.lookup', 3600, function () {
            return Product::orderBy('name')->select('id', 'name')->get();
        });

        $rewardables = cache()->remember('rewardables.lookup', 3600, function () {
            return RewardableEntity::orderBy('type')->select('id', 'type')->get();
        });

        return view('livewire.product.product-rewards-index', compact('rewards', 'products', 'rewardables'));
    }

    // form state
    public $form = [
        'product_id' => null,
        'reward_type' => null,
        'amount' => null,
        'rewardable_entity_id' => null,
        'level' => 0,
    ];

    public $isEdit = false;
    public $selectedReward = null;
    public $pendingDeleteId = null;

    protected $rules = [
        'form.product_id' => 'required|exists:products,id',
        'form.rewardable_entity_id' => 'nullable|exists:rewardable_entities,id',
        'form.level' => 'nullable|integer',
        'form.amount' => 'nullable|numeric',
        'form.reward_type' => 'nullable',
    ];

    public function create()
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->dispatch('modal-show', name: 'product-reward-modal');
    }

    public function edit($id)
    {
        $reward = ProductReward::findOrFail($id);
        $this->form = [
            'product_id' => $reward->product_id,
            'reward_type' => $reward->reward_type,
            'amount' => $reward->amount,
            'rewardable_entity_id' => $reward->rewardable_entity_id,
            'level' => $reward->level ?? 0,
        ];
        $this->isEdit = true;
        $this->selectedReward = $reward;
        $this->dispatch('modal-show', name: 'product-reward-modal');
    }

    public function showDetail($id)
    {
        $this->selectedReward = ProductReward::with('product')->findOrFail($id);
        $this->dispatch('modal-show', name: 'product-reward-detail-modal');
    }

    public function save()
    {
        $this->validate();

        // sanitize
        foreach (['reward_type','amount','level'] as $k) {
            if (array_key_exists($k, $this->form) && $this->form[$k] === '') {
                $this->form[$k] = null;
            }
        }

        if (isset($this->form['rewardable_entity_id']) && $this->form['rewardable_entity_id'] === '') {
            $this->form['rewardable_entity_id'] = null;
        }

        if ($this->isEdit && $this->selectedReward) {
            $this->selectedReward->update($this->form);
            $this->dispatch('toast', variant: 'success', text: 'Product reward updated successfully.');
        } else {
            ProductReward::create($this->form);
            $this->dispatch('toast', variant: 'success', text: 'Product reward created successfully.');
        }

        $this->dispatch('modal-close', name: 'product-reward-modal');
        $this->resetForm();
        $this->resetPage();
    }

    public function delete($id)
    {
        $r = ProductReward::findOrFail($id);
        $r->delete();
        $this->dispatch('toast', variant: 'danger', text: 'Product reward deleted.');
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->pendingDeleteId = $id;
        $this->dispatch('modal-show', name: 'confirm-delete-modal');
    }

    public function destroy()
    {
        if ($this->pendingDeleteId) {
            $this->delete($this->pendingDeleteId);
        }
        $this->pendingDeleteId = null;
        $this->dispatch('modal-close', name: 'confirm-delete-modal');
    }

    public function cancelDelete()
    {
        $this->pendingDeleteId = null;
        $this->dispatch('modal-close', name: 'confirm-delete-modal');
    }

    protected function resetForm()
    {
        $this->form = [
            'product_id' => null,
            'reward_type' => null,
            'amount' => null,
            'rewardable_entity_id' => null,
            'level' => 0,
        ];
        $this->selectedReward = null;
    }
}

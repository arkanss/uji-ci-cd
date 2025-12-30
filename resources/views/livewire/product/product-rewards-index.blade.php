<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-6">
        <flux:heading size="xl" class="mb-1">Product Rewards</flux:heading>
        <flux:subheading>Manage product reward mappings and values</flux:subheading>
    </div>

    <div class="flex items-center justify-end mb-4">
        <flux:button variant="primary" icon="plus" wire:click="create">New product reward</flux:button>
    </div>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div></div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Product</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Reward type</th>
                        
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Created at</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Updated at</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($rewards as $reward)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors" wire:key="{{ $reward->id }}">
                            <td class="px-4 py-3 text-sm font-medium">{{ optional($reward->product)->name ?? $reward->product_id }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-zinc-600">{{ $reward->amount }}</td>
                            <td class="px-4 py-3 text-sm">{{ $reward->reward_type ?? '-' }}</td>
                            
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ $reward->created_at }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ $reward->updated_at }}</td>
                            <td class="px-4 py-3 text-right text-zinc-500">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye" wire:click="showDetail('{{ $reward->id }}')">Detail</flux:menu.item>
                                        <flux:menu.item icon="pencil-square" wire:click="edit('{{ $reward->id }}')">Edit</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete('{{ $reward->id }}')">Delete</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-zinc-500 italic">No product rewards found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $rewards->links() }}
        </div>
    </div>

        <flux:modal name="product-reward-modal" class="md:w-[600px]">
            <form wire:submit.prevent="save" class="flex flex-col h-full max-h-[85vh]">
                <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-700 flex-shrink-0">
                    <flux:heading size="lg">{{ $isEdit ? 'Update Product Reward' : 'New Product Reward' }}</flux:heading>
                    <flux:subheading class="mt-1">Associate product with reward</flux:subheading>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div class="px-6 py-6 space-y-6">
                        <div class="space-y-2">
                            <flux:label>Product*</flux:label>
                            <flux:select wire:model="form.product_id">
                                <option value="">Select product</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="form.product_id" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <flux:label>Reward type</flux:label>
                                <flux:input wire:model="form.reward_type" placeholder="Reward type" />
                                <flux:error name="form.reward_type" />
                            </div>
                            <div class="space-y-2">
                                <flux:label>Amount</flux:label>
                                <flux:input type="number" wire:model="form.amount" placeholder="0" />
                                <flux:error name="form.amount" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <flux:label>Rewardable Entity</flux:label>
                                <flux:select wire:model="form.rewardable_entity_id">
                                    <option value="">Select entity</option>
                                    @foreach($rewardables as $r)
                                        <option value="{{ $r->id }}">{{ $r->type }} — {{ $r->id }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="form.rewardable_entity_id" />
                            </div>

                            <div class="space-y-2">
                                <flux:label>Level</flux:label>
                                <flux:input type="number" wire:model="form.level" placeholder="0" />
                                <flux:error name="form.level" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 flex items-center justify-end gap-3 flex-shrink-0">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" icon="check">{{ $isEdit ? 'Save Changes' : 'Create' }}</flux:button>
                </div>
            </form>
        </flux:modal>

        <flux:modal name="product-reward-detail-modal" class="md:w-[600px]">
            @if($selectedReward)
                <div class="space-y-6 p-6">
                    <div>
                        <flux:heading size="lg">Reward Detail</flux:heading>
                        <flux:subheading class="mt-1">Details for selected product reward</flux:subheading>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-100 dark:border-zinc-800">
                        <div>
                            <span class="text-xs text-zinc-500 font-bold">Product</span>
                            <div class="text-sm">{{ $selectedReward->product?->name ?? $selectedReward->product_id }}</div>
                        </div>
                        
                        <div>
                            <span class="text-xs text-zinc-500 font-bold">Rewardable Entity</span>
                            <div class="text-sm">{{ $selectedReward->rewardable_entity_id ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="text-xs text-zinc-500 font-bold">Level</span>
                            <div class="text-sm">{{ $selectedReward->level ?? 0 }}</div>
                        </div>
                        <div>
                            <span class="text-xs text-zinc-500 font-bold">Reward type</span>
                            <div class="text-sm">{{ $selectedReward->reward_type ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="text-xs text-zinc-500 font-bold">Amount</span>
                            <div class="text-sm">{{ $selectedReward->amount }}</div>
                        </div>
                    </div>

                    <div class="flex pt-4">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost">Close</flux:button>
                        </flux:modal.close>
                    </div>
                </div>
            @else
                <div class="p-10 flex justify-center items-center flex-col gap-2">
                    <div class="animate-spin size-6 border-2 border-blue-600 border-t-transparent rounded-full"></div>
                    <span class="text-zinc-400 text-sm">Loading details...</span>
                </div>
            @endif
        </flux:modal>

        <flux:modal name="confirm-delete-modal" class="md:w-[480px]">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                        <flux:icon.trash class="size-6" />
                    </div>

                    <div class="min-w-0">
                        <flux:heading size="lg" class="mb-1">Delete product reward</flux:heading>
                        <p class="text-sm text-white">Apakah Anda yakin ingin menghapus data product reward ini?</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="cancelDelete">Cancel</flux:button>
                    <flux:button variant="danger" wire:click="destroy" icon="trash">Delete</flux:button>
                </div>
            </div>
        </flux:modal>
</div>

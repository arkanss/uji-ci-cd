<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Operational Costs</flux:heading>
            <flux:subheading>Record and manage daily operational expenses</flux:subheading>
        </div>

        <flux:modal.trigger name="cost-modal">
            <flux:button variant="primary" icon="plus" wire:click="resetForm">Add Record</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="space-y-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search costs..."
            class="max-w-sm" />

        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Title</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Created By</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($costs as $cost)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            wire:key="{{ $cost->id }}">
                            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $cost->title }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                Rp {{ number_format($cost->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500">
                                {{ \Carbon\Carbon::parse($cost->date)->format('M d, Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($cost->creator)
                                    <flux:badge icon="user-circle" color="indigo" variant="subtle">
                                        {{ $cost->creator->name }}
                                    </flux:badge>
                                @else
                                    <flux:badge icon="cpu-chip" color="amber" variant="subtle">
                                        System
                                    </flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />
                                    <flux:menu>
                                        @if ($cost->attachments_url)
                                            <flux:menu.item icon="paper-clip"
                                                href="{{ Storage::url($cost->attachments_url) }}" target="_blank">View
                                                Attachment</flux:menu.item>
                                        @endif
                                        <flux:menu.item icon="pencil-square" wire:click="edit('{{ $cost->id }}')">
                                            Edit</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="delete('{{ $cost->id }}')"
                                            wire:confirm="Delete this record?">Delete</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-zinc-500">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $costs->links() }}</div>
    </div>

    <flux:modal name="cost-modal" class="md:w-[500px]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Update Record' : 'Add Operational Cost' }}</flux:heading>
            </div>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Title</flux:label>
                    <flux:input wire:model="title" placeholder="e.g. Electricity Bill" />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>Amount (Rp)</flux:label>
                    <flux:input type="number" wire:model="amount" />
                    <flux:error name="amount" />
                </flux:field>

                <flux:field>
                    <flux:label>Date</flux:label>
                    <flux:input type="date" wire:model="date" />
                    <flux:error name="date" />
                </flux:field>

                <flux:field>
                    <flux:label>Attachment (Image/PDF)</flux:label>
                    <flux:input type="file" wire:model="attachment" />
                    @if ($oldAttachment && !$attachment)
                        <p class="text-xs text-zinc-500 mt-1">Current: {{ basename($oldAttachment) }}</p>
                    @endif
                    <flux:error name="attachment" />
                </flux:field>
            </div>

            <div class="flex gap-3 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Record</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

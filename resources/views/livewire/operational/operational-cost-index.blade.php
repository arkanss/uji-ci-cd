<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Operational Costs</flux:heading>
            <flux:subheading>Record and manage daily operational expenses</flux:subheading>
        </div>

        <flux:modal.trigger name="cost-modal">
            {{-- Reset Form akan mengeset isEdit = false --}}
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
                                {{ \Carbon\Carbon::parse($cost->date)->format('M d, Y') }}
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
                <flux:heading size="lg">{{ $isEdit ? 'Update Record' : 'Add Operational Cost' }}</flux:heading>
            </div>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Title</flux:label>
                    <flux:input wire:model="title" placeholder="e.g. Electricity Bill" />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>Amount</flux:label>

                    <div x-data="{
                        rawAmount: @entangle('amount'),
                    
                        get formatted() {
                            if (!this.rawAmount) return '';
                            return this.rawAmount.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        },
                    
                        updateValue(e) {
                            let val = e.target.value.replace(/\D/g, ''); // Ambil angka saja
                            this.rawAmount = val; // Kirim ke Livewire
                        }
                    }">
                        <flux:input.group>
                            <flux:input.group.prefix>Rp</flux:input.group.prefix>

                            <flux:input type="text" placeholder="0" x-bind:value="formatted"
                                x-on:input="updateValue($event)" />
                        </flux:input.group>
                    </div>

                    <flux:error name="amount" />
                </flux:field>

                <flux:field>
                    <flux:label>Date</flux:label>
                    <flux:input type="date" wire:model="date" />
                    <flux:error name="date" />
                </flux:field>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Attachment (Image/PDF)
                    </label>

                    <label
                        class="relative flex flex-col items-center justify-center w-full h-32 border border-dashed rounded-lg cursor-pointer bg-zinc-900/40 border-zinc-700 text-zinc-400 hover:bg-zinc-900/60 transition overflow-hidden">

                        @if ($attachment && !is_string($attachment))
                            @php
                                $extension = $attachment->guessExtension();
                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp

                            @if ($isImage)
                                <img src="{{ $attachment->temporaryUrl() }}"
                                    class="absolute inset-0 w-full h-full object-cover" />
                            @else
                                <div class="flex flex-col items-center z-10">
                                    <flux:icon icon="document-text" size="md" />
                                    <span class="text-xs mt-2">{{ $attachment->getClientOriginalName() }}</span>
                                </div>
                            @endif
                        @elseif($isEdit && $attachment && is_string($attachment))
                            @php
                                $extension = pathinfo($attachment, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp

                            @if ($isImage)
                                <img src="{{ asset('storage/' . $attachment) }}"
                                    class="absolute inset-0 w-full h-full object-cover" />
                            @else
                                <div class="flex flex-col items-center z-10 text-indigo-400">
                                    <flux:icon icon="document-check" size="md" />
                                    <span class="text-xs mt-2 italic">Current: {{ basename($attachment) }}</span>
                                </div>
                            @endif
                        @else
                            <div class="flex flex-col items-center z-10">
                                <flux:icon icon="arrow-up-tray" size="sm" class="mb-2" />
                                <span class="text-sm">
                                    Drag & Drop or <span class="text-white font-medium">Browse</span>
                                </span>
                                <p class="text-[10px] mt-1 text-zinc-500">Max size: 2MB (JPG, PNG, PDF)</p>
                            </div>
                        @endif

                        <input type="file" wire:model="attachment" class="hidden" accept="image/*,.pdf" />

                        <div wire:loading wire:target="attachment"
                            class="absolute inset-0 flex items-center justify-center bg-black/60 z-20 rounded-lg text-xs">
                            <div class="flex items-center gap-2">
                                <div
                                    class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full">
                                </div>
                                Uploading...
                            </div>
                        </div>
                    </label>

                    <flux:error name="attachment" />
                </div>
            </div>
            <div class="flex gap-3 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $isEdit ? 'Update Record' : 'Save Record' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>

<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Operational Costs</flux:heading>
            <flux:subheading>Record and manage daily operational expenses</flux:subheading>
        </div>

        <flux:modal.trigger name="cost-modal">
            <div>
                <flux:button variant="primary" icon="plus" wire:click="resetForm">Add Record</flux:button>
            </div>
        </flux:modal.trigger>
    </div>
    <div class="space-y-4">
        <flux:input wire:model.debounce.300ms="search" icon="magnifying-glass" placeholder="Search costs..."
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
                            wire:key="{{ $cost->id }}" data-id="{{ $cost->id }}">
                            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $cost->title }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                Rp {{ number_format($cost->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500">
                                {{ \Carbon\Carbon::parse($cost->date)->format('d M Y H:i') }}
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
                                               href="{{ $cost->attachments_url }}" target="_blank">View
                                                Attachment</flux:menu.item>
                                        @endif
                                        <flux:menu.item icon="pencil-square" wire:click="openEdit('{{ $cost->id }}')">Edit</flux:menu.item>
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

    <flux:modal name="cost-modal" class="w-full max-w-6xl space-y-0 p-0">
        <form wire:submit.prevent="save" class="space-y-6 p-6">
            <div>
                <flux:heading size="lg">{{ $isEdit ? 'Update Record' : 'Create Operational Cost(s)' }}</flux:heading>
                <flux:subheading class="mt-1">Record and manage daily operational expenses</flux:subheading>
            </div>

            <div class="grid grid-cols-1 gap-4">
                @if($isEdit)
                    <div class="p-4 rounded bg-transparent">
                        <flux:field>
                            <flux:label>Title</flux:label>
                            <flux:input wire:model.defer="title" placeholder="e.g. Electricity Bill" />
                            <flux:error name="title" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="mt-3">Amount</flux:label>
                            <div x-data="{ rawAmount: @entangle('amount'), get formatted(){ if(!this.rawAmount) return ''; return String(this.rawAmount).replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.'); }, updateValue(e){ let val = e.target.value.replace(/\D/g,''); this.rawAmount = val; } }">
                                <flux:input.group>
                                    <flux:input.group.prefix>Rp</flux:input.group.prefix>
                                    <flux:input type="text" placeholder="0" x-bind:value="formatted" x-on:input="updateValue($event)" />
                                </flux:input.group>
                            </div>
                            <flux:error name="amount" />
                        </flux:field>

                            <flux:field>
                                <flux:label class="mt-3">Date & Time (WIB)</flux:label>
                                <flux:input type="datetime-local" wire:model.defer="date" />
                                <flux:error name="date" />
                            </flux:field>

                        <div class="space-y-2">
                            <flux:label class="mt-3">Attachment (Image/PDF)</flux:label>
                            <label class="relative flex flex-col items-center justify-center w-full h-32 border border-dashed rounded-lg cursor-pointer bg-zinc-50 dark:bg-zinc-900 border-zinc-100 dark:border-zinc-800 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition overflow-hidden">
                                @if ($attachment && !is_string($attachment) && method_exists($attachment,'temporaryUrl'))
                                    @php $extension = $attachment->guessExtension(); $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']); @endphp
                                    @if ($isImage)
                                        <img src="{{ $attachment->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-contain object-center z-10" style="display:block" />
                                    @else
                                        <div class="flex flex-col items-center z-10"><flux:icon icon="document-text" size="md" /><span class="text-xs mt-2">{{ $attachment->getClientOriginalName() }}</span></div>
                                    @endif
                                @elseif ($attachment && is_string($attachment))
                                    @php $extension = pathinfo($attachment, PATHINFO_EXTENSION); $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']); @endphp
                                    @if ($isImage)
                                        <img src="{{ $attachment }}" class="absolute inset-0 w-full h-full object-contain object-center z-10" style="display:block" />
                                    @else
                                        <div class="flex flex-col items-center z-10 text-indigo-400"><flux:icon icon="document-check" size="md" /><span class="text-xs mt-2 italic">Current: {{ basename($attachment) }}</span></div>
                                    @endif
                                @else
                                    <div class="flex flex-col items-center z-10" wire:loading.class="hidden" wire:target="attachment"><flux:icon icon="arrow-up-tray" size="sm" class="mb-2" /><span class="text-sm">Drag & Drop or <span class="text-white font-medium">Browse</span></span><p class="text-[10px] mt-1 text-zinc-500">Max size: 5MB (JPG, PNG, PDF)</p></div>
                                @endif

                                    <input type="file" wire:model="attachment" class="hidden" accept="image/*,.pdf" />

                                <div wire:loading wire:target="attachment" class="absolute inset-0 flex items-center justify-center bg-black/60 z-20 rounded-lg text-xs"><div class="flex flex-col items-center justify-center gap-2 text-white text-sm"><div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div><span>Uploading...</span></div></div>
                            </label>
                            <flux:error name="attachment" />
                        </div>
                    </div>
                @else
                    @foreach($items as $index => $item)
                        <div class="p-4 rounded bg-transparent" wire:key="oc-item-modal-{{ $index }}">
                            <div class="flex items-start justify-between mb-3">
                                <div class="text-sm font-medium">Item #{{ $index + 1 }}</div>
                                <div>
                                    @if(count($items) > 1)
                                        <button type="button" wire:click.prevent="removeItem({{ $index }})" class="inline-flex items-center justify-center w-7 h-7 rounded-full text-red-600 hover:bg-red-50" title="Remove item">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <flux:field>
                                <flux:label>Title*</flux:label>
                                <flux:input type="text" wire:model.defer="items.{{ $index }}.title" placeholder="e.g. Electricity bill" />
                                <flux:error name="items.{{ $index }}.title" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="mt-3">Amount*</flux:label>
                                <div x-data="{ rawAmount: @entangle('items.'. $index .'.amount'), get formatted(){ if(!this.rawAmount) return ''; return String(this.rawAmount).replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.'); }, updateValue(e){ let val = e.target.value.replace(/\D/g,''); this.rawAmount = val; } }">
                                    <flux:input.group>
                                        <flux:input.group.prefix>Rp</flux:input.group.prefix>
                                        <flux:input type="text" placeholder="0" x-bind:value="formatted" x-on:input="updateValue($event)" />
                                    </flux:input.group>
                                </div>
                                <flux:error name="items.{{ $index }}.amount" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="mt-3">Date & Time*</flux:label>
                                <flux:input type="datetime-local" wire:model.defer="items.{{ $index }}.date" />
                                <flux:error name="items.{{ $index }}.date" />
                            </flux:field>

                            <div class="space-y-2">
                                <flux:label class="mt-3">Attachment (Image/PDF)</flux:label>
                                <label class="relative flex flex-col items-center justify-center w-full h-32 border border-dashed rounded-lg cursor-pointer bg-zinc-900/40 border-zinc-700 text-zinc-400 hover:bg-zinc-900/60 transition overflow-hidden">
                                    @php $att = $item['attachment'] ?? null; @endphp
                                    @if ($att && !is_string($att) && method_exists($att,'temporaryUrl'))
                                        @php $extension = $att->guessExtension(); $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']); @endphp
                                        @if ($isImage)
                                            <img id="preview-{{ $index }}" src="{{ $att->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-contain object-center z-10" style="display:block" />
                                        @else
                                            <div class="flex flex-col items-center z-10"><flux:icon icon="document-text" size="md" /><span class="text-xs mt-2">{{ $att->getClientOriginalName() }}</span></div>
                                        @endif
                                    @elseif ($att && is_string($att))
                                        @php $extension = pathinfo($att, PATHINFO_EXTENSION); $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']); @endphp
                                        @if ($isImage)
                                            <img id="preview-{{ $index }}" src="{{ $att }}" class="absolute inset-0 w-full h-full object-contain object-center z-10" style="display:block" />
                                        @else
                                            <div class="flex flex-col items-center z-10 text-indigo-400"><flux:icon icon="document-check" size="md" /><span class="text-xs mt-2 italic">Current: {{ basename($att) }}</span></div>
                                        @endif
                                    @else
                                        <div class="flex flex-col items-center z-10" wire:loading.class="hidden" wire:target="items.{{ $index }}.attachment"><flux:icon icon="arrow-up-tray" size="sm" class="mb-2" /><span class="text-sm">Drag & Drop or <span class="text-white font-medium">Browse</span></span><p class="text-[10px] mt-1 text-zinc-500">Max size: 5MB (JPG, PNG, PDF)</p></div>
                                    @endif

                                    <img id="preview-{{ $index }}" class="absolute inset-0 w-full h-full object-contain object-center z-10" style="display:none" />
                                    <input type="file" wire:model="items.{{ $index }}.attachment" wire:key="items-{{ $index }}-attachment" onchange="previewFile(this.files[0], 'preview-{{ $index }}')" class="hidden" accept="image/*,.pdf" />

                                    <div wire:loading wire:target="items.{{ $index }}.attachment" class="absolute inset-0 flex items-center justify-center bg-black/60 z-20 rounded-lg text-xs"><div class="flex flex-col items-center justify-center gap-2 text-white text-sm"><div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div><span>Uploading...</span></div></div>
                                </label>
                                <flux:error name="items.{{ $index }}.attachment" />
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center">
                    @unless($isEdit)
                        <flux:button type="button" variant="ghost" wire:click.prevent="addItem">Add Items</flux:button>
                    @endunless
                </div>
                <div class="flex items-center gap-3">
                    <flux:button type="submit" variant="primary">{{ $isEdit ? 'Update Operational Cost' : 'Save Operational Cost' }}</flux:button>
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost" wire:click.prevent="resetForm">Cancel</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </form>
    </flux:modal>

    <script>
    function previewFile(file, id) {
        if (!file) return;
        try {
            const img = document.getElementById(id);
            if (!img) return;
            const url = URL.createObjectURL(file);
            img.src = url;
            img.style.display = 'block';
        } catch (e) { console.error(e); }
    }

    // Fallback: if the menu component doesn't forward wire:click, handle clicks on 'Edit' text and emit Livewire event
    document.addEventListener('click', function (e) {
        try {
            const t = e.target;
            if (!t || !t.textContent) return;
            if (t.textContent.trim() !== 'Edit') return;
            const row = t.closest('tr[wire\\:key]') || t.closest('tr');
            if (!row) return;
            let id = row.getAttribute('wire:key') || row.getAttribute('data-id');
            if (!id) return;
            if (window.Livewire && typeof Livewire.emit === 'function') {
                Livewire.emit('openEdit', id);
            } else if (window.livewire && typeof window.livewire.emit === 'function') {
                window.livewire.emit('openEdit', id);
            }
        } catch (err) { console.warn(err); }
    });
    </script>
</div>

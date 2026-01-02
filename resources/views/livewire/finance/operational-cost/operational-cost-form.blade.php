<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-6">
        <flux:heading size="xl">{{ $editingId ? 'Edit' : 'Create' }}</flux:heading>
        <flux:subheading>
            Manage expenses and attachments
        </flux:subheading>
    </div>

    @if(session('oc_success'))
    <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 rounded">
        <p class="text-green-800 dark:text-green-200">{{ session('oc_success') }}</p>
    </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        <x-ui.card>
            <div class="grid grid-cols-1 gap-4">
                @foreach($items as $index => $item)
                    <div class="p-4 rounded border-0 bg-transparent" wire:key="oc-item-{{ $index }}">
                        <div class="flex items-start justify-between mb-3">
                            <div class="text-sm font-medium">Item #{{ $index + 1 }}</div>
                            <div>
                                @if(count($items) > 1)
                                    <button type="button" wire:click.prevent="removeItem({{ $index }})" class="inline-flex items-center justify-center w-7 h-7 rounded-full text-red-600 hover:bg-red-50" title="Remove item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
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
                            <div x-data="{
                                rawAmount: @entangle("items.{$index}.amount"),
                                get formatted() {
                                    if (!this.rawAmount) return '';
                                    return this.rawAmount.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                },
                                updateValue(e) {
                                    let val = e.target.value.replace(/\D/g, '');
                                    this.rawAmount = val;
                                }
                            }">
                                <flux:input.group>
                                    <flux:input.group.prefix>Rp</flux:input.group.prefix>
                                    <flux:input type="text" placeholder="0" x-bind:value="formatted" x-on:input="updateValue($event)" />
                                </flux:input.group>
                            </div>
                            <flux:error name="items.{{ $index }}.amount" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="mt-3">Date*</flux:label>
                            <flux:input type="date" wire:model.defer="items.{{ $index }}.date" />
                            <flux:error name="items.{{ $index }}.date" />
                        </flux:field>

                        <div class="space-y-2">
                            <flux:label class="mt-3">Attachment (Image/PDF)</flux:label>

                            <label class="relative flex flex-col items-center justify-center w-full h-32 border border-dashed rounded-lg cursor-pointer bg-zinc-900/40 border-zinc-700 text-zinc-400 hover:bg-zinc-900/60 transition overflow-hidden">

                                @php
                                    $att = $item['attachment'] ?? null;
                                @endphp

                                @if ($att && !is_string($att) && method_exists($att, 'temporaryUrl'))
                                    @php
                                        $extension = $att->guessExtension();
                                        $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
                                    @endphp

                                    @if ($isImage)
                                        <img id="preview-{{ $index }}" src="{{ $att->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-contain object-center z-10" style="display:block" />
                                    @else
                                        <div class="flex flex-col items-center z-10">
                                            <flux:icon icon="document-text" size="md" />
                                            <span class="text-xs mt-2">{{ $att->getClientOriginalName() }}</span>
                                        </div>
                                    @endif

                                @elseif ($att && is_string($att))
                                    @php
                                        $extension = pathinfo($att, PATHINFO_EXTENSION);
                                        $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
                                    @endphp

                                    @if ($isImage)
                                        <img id="preview-{{ $index }}" src="{{ $att }}" class="absolute inset-0 w-full h-full object-contain object-center z-10" style="display:block" />
                                    @else
                                        <div class="flex flex-col items-center z-10 text-indigo-400">
                                            <flux:icon icon="document-check" size="md" />
                                            <span class="text-xs mt-2 italic">Current: {{ basename($att) }}</span>
                                        </div>
                                    @endif

                                @else
                                    <div class="flex flex-col items-center z-10" wire:loading.class="hidden" wire:target="items.{{ $index }}.attachment">
                                        <flux:icon icon="arrow-up-tray" size="sm" class="mb-2" />
                                        <span class="text-sm">Drag & Drop or <span class="text-white font-medium">Browse</span></span>
                                        <p class="text-[10px] mt-1 text-zinc-500">Max size: 2MB (JPG, PNG, PDF)</p>
                                    </div>
                                @endif

                                <img id="preview-{{ $index }}" class="absolute inset-0 w-full h-full object-contain object-center z-10" style="display:none" />
                                <input type="file" wire:model="items.{{ $index }}.attachment" wire:key="items-{{ $index }}-attachment" onchange="previewFile(this.files[0], 'preview-{{ $index }}')" class="hidden" accept="image/*,.pdf" />

                                <div wire:loading wire:target="items.{{ $index }}.attachment" class="absolute inset-0 flex items-center justify-center bg-black/60 z-20 rounded-lg text-xs">
                                    <div class="flex flex-col items-center justify-center gap-2 text-white text-sm">
                                        <div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
                                        <span>Uploading...</span>
                                    </div>
                                </div>
                            </label>

                            <flux:error name="items.{{ $index }}.attachment" />
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <flux:button type="button" variant="ghost" wire:click.prevent="addItem">Add Operational Cost</flux:button>
                </div>
            </div>
        </x-ui.card>

        <div class="sticky bottom-0 left-0 right-0 z-10 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 pt-4 pb-4 -mx-6 px-6">
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Update Operational Cost' : 'Save Operational Cost' }}</flux:button>
                <a href="{{ route('finance.operational-cost.index') }}">
                    <flux:button type="button" variant="ghost">Cancel</flux:button>
                </a>
                <flux:spacer />
                <span class="text-xs text-zinc-500 self-center">* Required fields</span>
            </div>
        </div>
    </form>
</div>

    <script>
    function previewFile(file, id) {
        if (!file) return;
        try {
            const img = document.getElementById(id);
            if (!img) return;
            const url = URL.createObjectURL(file);
            img.src = url;
            img.style.display = 'block';
        } catch (e) {
            console.error(e);
        }
    }
    </script>

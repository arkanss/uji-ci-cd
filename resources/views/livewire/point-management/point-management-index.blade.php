<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Point Management</flux:heading>
            <flux:subheading>Manage your point configurations and currency rates.</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">Add Point</flux:button>
    </div>

    <div class="space-y-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search points..."
            class="max-w-sm" />

        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Icon</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Key / Name</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Rate (IDR)</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($points as $point)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-4 py-3">
                                @if ($point->icon)
                                    <img src="{{ $point->icon }}" class="h-8 w-8 object-contain rounded">
                                @endif

                            </td>

                            <td class="px-4 py-3 text-sm">
                                <div class="font-semibold">{{ $point->name }} ({{ $point->abbr }})</div>
                                <div class="text-xs text-zinc-500">{{ $point->key }}</div>
                            </td>

                            <td class="px-4 py-3 text-sm text-zinc-600">
                                Rp {{ number_format($point->value_idr, 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3">
                                <flux:badge :color="$point->status->value === 1 ? 'green' : 'zinc'" size="sm">
                                    {{ $point->status->name }}
                                </flux:badge>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <flux:button variant="ghost" size="sm" icon="pencil-square"
                                    wire:click="edit('{{ $point->id }}')" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-zinc-500">
                                No points found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $points->links() }}</div>
    </div>

    <flux:modal name="point-modal" class="md:w-[900px] space-y-6">
        <form wire:submit="save" class="space-y-6">

            <div class="pb-4">
                <flux:heading size="lg">
                    {{ $isEditing ? 'Update Point' : 'Create New Point' }}
                </flux:heading>
                <flux:subheading class="text-sm text-zinc-500">
                    Configure your point behavior and value.
                </flux:subheading>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <flux:field>
                    <flux:label>Point Icon</flux:label>

                    <div class="relative group w-full">
                        <div
                            class="w-full h-36 rounded-xl border-2 border-dashed
                            border-zinc-300 dark:border-zinc-700
                            bg-zinc-50 dark:bg-zinc-800
                            flex items-center justify-center
                            overflow-hidden transition-all
                            group-hover:border-primary-500">

                            @if ($icon)
                                <img src="{{ $icon->temporaryUrl() }}" class="object-contain w-full h-full p-3">
                            @elseif ($oldIcon)
                                <img src="{{ $oldIcon }}" class="object-contain w-full h-full p-3">
                            @else
                                <div class="flex flex-col items-center gap-2 text-zinc-400">
                                    <flux:icon.photo class="size-8" />
                                    <span class="text-sm">Click to upload icon</span>
                                </div>
                            @endif
                        </div>

                        <input type="file" wire:model="icon" accept="image/*"
                            class="absolute inset-0 opacity-0 cursor-pointer">
                    </div>

                    <div wire:loading wire:target="icon" class="text-xs text-primary-600 mt-1">
                        Uploading...
                    </div>

                    <flux:error name="icon" />
                </flux:field>


                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Key</flux:label>
                        <flux:input wire:model="key" />
                        <flux:error name="key" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name" />
                        <flux:error name="name" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Abbreviation</flux:label>
                    <flux:input wire:model="abbr" />
                    <flux:error name="abbr" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="description" />
                    <flux:error name="description" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <flux:label>Rate (IDR)</flux:label>

                        <div x-data="{
                            rawPrice: @entangle('value_idr'),
                            get formatted() {
                                if (!this.rawPrice) return '';
                                return this.rawPrice
                                    .toString()
                                    .replace(/\D/g, '')
                                    .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            },
                            updateValue(e) {
                                this.rawPrice = e.target.value.replace(/\D/g, '');
                            }
                        }">
                            <flux:input.group>
                                <flux:input.group.prefix>Rp</flux:input.group.prefix>
                                <flux:input type="text" x-bind:value="formatted"
                                    x-on:input="updateValue($event)" />
                            </flux:input.group>
                        </div>

                        <flux:error name="value_idr" />
                    </div>

                    <flux:field>
                        <flux:label>Value Parent</flux:label>
                        <flux:input type="number" step="0.001" wire:model="value_parent" />
                        <flux:error name="value_parent" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Scope Service</flux:label>

                    <div x-data="{
                        selected: @entangle('scope_service'),
                        options: [
                            { value: 'local_place', label: 'Local Place' },
                            { value: 'local_health', label: 'Local Health' },
                            { value: 'khas', label: 'KHAS Indonesia' },
                        ],
                        toggle(val) {
                            this.selected.includes(val) ?
                                this.selected = this.selected.filter(v => v !== val) :
                                this.selected.push(val)
                        }
                    }" class="flex flex-wrap gap-2">
                        <template x-for="option in options">
                            <button type="button" @click="toggle(option.value)"
                                :class="selected.includes(option.value) ?
                                    'bg-blue-600 text-white' :
                                    'bg-white text-zinc-700 border'"
                                class="px-4 py-2 rounded-full border text-xs">
                                <span x-text="option.label"></span>
                            </button>
                        </template>
                    </div>

                    <flux:error name="form.scope_service" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </flux:select>
                </flux:field>

                <flux:checkbox wire:model="is_exchangeable" label="Is Exchangeable?" />
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">
                    Save Point
                </flux:button>
            </div>

        </form>
    </flux:modal>
</div>

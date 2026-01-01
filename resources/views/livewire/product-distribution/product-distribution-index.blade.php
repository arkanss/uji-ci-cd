<div class="p-6 max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Product Distribution</flux:heading>
            <flux:subheading>Kelola penempatan produk dan stok di setiap merchant</flux:subheading>
        </div>
        <div class="flex gap-3">
            <flux:button variant="primary" icon="plus" wire:click="create">Distribusi Produk</flux:button>
        </div>
    </div>

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Merchant</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Product</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Current Stock</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Total Distributed</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Status</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($distributions as $item)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50" wire:key="{{ $item->id }}">
                        <td class="px-4 py-3 text-sm font-medium">{{ $item->merchant?->name }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->product?->name }}</td>
                        <td class="px-4 py-3 text-center">
                            <flux:badge size="sm" variant="outline"
                                color="{{ $item->stock > 10 ? 'zinc' : 'warning' }}">
                                {{ number_format($item->stock) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-zinc-600 dark:text-zinc-400">
                            {{ number_format($item->total_stock) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColor = 'zinc';

                                if ($item->status == 1) {
                                    $statusColor = 'green';
                                } elseif ($item->status == 0) {
                                    $statusColor = 'red';
                                }
                            @endphp

                            <flux:badge size="sm" color="{{ $statusColor }}" variant="solid">
                                {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </td>

                        <td class="px-4 py-3 text-right flex justify-end gap-2">
                            <flux:button size="sm" variant="ghost" icon="eye"
                                wire:click="showDetail('{{ $item->id }}')" />
                            <flux:button size="sm" variant="ghost" icon="pencil-square"
                                wire:click="edit('{{ $item->id }}')" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-zinc-500">No data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 text-white">{{ $distributions->links() }}</div>

    <flux:modal name="distribution-modal" class="w-full max-w-6xl space-y-0 p-0">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Distribusi Produk Baru</flux:heading>
                <flux:subheading>Tambahkan alokasi stok ke berbagai merchant.</flux:subheading>
            </div>

            <div class="space-y-4 max-h-[420px] overflow-y-auto px-1">
                @foreach ($items as $index => $item)
                    <div class="relative rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-900/40 p-4"
                        wire:key="distribution-{{ $index }}">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-sm font-medium text-zinc-200">
                                Distribution #{{ $index + 1 }}
                            </div>

                            @if (count($items) > 1)
                                <flux:button variant="ghost" icon="trash" color="danger" size="sm"
                                    wire:click="removeDistribution({{ $index }})" />
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                            <flux:select label="Merchant" wire:model.live="items.{{ $index }}.merchant_id"
                                placeholder="Pilih Merchant">
                                @foreach ($merchants as $m)
                                    <option value="{{ $m->user_id }}">{{ $m->name }}</option>
                                @endforeach
                            </flux:select>

                            <flux:select label="Product" wire:model.live="items.{{ $index }}.product_id"
                                placeholder="Pilih Produk">
                                <option value="">-- Pilih Produk --</option>
                                @foreach ($products as $p)
                                    <option value="{{ (string) $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </flux:select>

                            <flux:input label="Quantity" type="number" min="1"
                                wire:model.live="items.{{ $index }}.stock" />

                            <flux:select label="Status" wire:model.live="items.{{ $index }}.status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </flux:select>

                        </div>


                        @error("items.$index.merchant_id")
                            <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
                        @enderror
                        @error("items.$index.product_id")
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <flux:button variant="ghost" icon="plus" wire:click="addDistribution"
                class="w-full border-dashed border-2">
                Tambah Baris Distribusi
            </flux:button>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="saveBulk">Simpan Semua</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="edit-modal" class="md:w-[500px]">
        <form wire:submit="saveEdit" class="space-y-6">
            <flux:heading size="lg">Edit Distribusi</flux:heading>
            <div class="space-y-4">
                <flux:select label="Merchant" wire:model="merchant_id">
                    @foreach ($merchants as $m)
                        <option value="{{ $m->user_id }}">{{ $m->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select label="Product" wire:model="product_id">
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </flux:select>
                <flux:input type="number" label="Stock" wire:model="stock" />
                <flux:select label="Status" wire:model="status">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </flux:select>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Update Changes</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="detail-modal" class="md:w-[600px] !p-0 overflow-hidden">
        <div class="bg-zinc-50 dark:bg-zinc-950 p-6 space-y-6">
            <flux:heading size="lg">Distribution Detail</flux:heading>
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-zinc-500 uppercase font-medium">Merchant</div>
                        <div class="text-sm font-bold mt-1 text-white">{{ $selectedItem?->merchant?->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 uppercase font-medium">Status</div>
                        <flux:badge size="sm" color="{{ $selectedItem?->status === 1 ? 'success' : 'danger' }}"
                            class="mt-1" variant="solid">
                            {{ $selectedItem?->status === 1 ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 uppercase font-medium">Product</div>
                        <div class="text-sm mt-1 text-white">{{ $selectedItem?->product?->name }}</div>
                    </div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm">
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div>
                        <div class="text-xs text-zinc-500 uppercase font-medium">Current Stock</div>
                        <div class="text-2xl font-bold text-green-600 mt-1">{{ number_format($selectedItem?->stock) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 uppercase font-medium">Total Distributed</div>
                        <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                            {{ number_format($selectedItem?->total_stock) }}</div>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" class="outline outline-2">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

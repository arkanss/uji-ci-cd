<div class="p-6 max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Product Stock Histories</flux:heading>
            <flux:subheading>Monitor riwayat perubahan stok barang secara real-time</flux:subheading>
        </div>

        <div class="w-full md:w-72">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Cari nama produk..." />
        </div>
    </div>

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Waktu Perubahan</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Produk</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Stok Awal</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Mutasi</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Stok Akhir</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($histories as $history)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                        wire:key="{{ $history->id }}">
                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $history->created_at->format('d/M/Y, H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $history->product?->name ?? 'Produk Terhapus' }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono text-zinc-500">
                            {{ number_format($history->stock_before) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $diff = $history->stock_after - $history->stock_before;

                                $badgeColor = match (true) {
                                    $diff > 0 => 'success',
                                    $diff < 0 => 'danger',
                                    default => 'zinc',
                                };

                                $prefix = $diff > 0 ? '+' : '';
                            @endphp

                            <flux:badge size="sm" color="{{ $badgeColor }}" variant="solid" class="font-bold">
                                {{ $prefix . $diff }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-bold text-zinc-900 dark:text-zinc-100">
                            {{ number_format($history->stock_after) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button size="sm" variant="ghost" icon="eye"
                                wire:click="showDetail('{{ $history->id }}')">
                                View
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-zinc-500 italic">Data history tidak
                            ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $histories->links() }}
    </div>

    <flux:modal name="stock-history-detail" class="md:w-[700px] !p-0 overflow-hidden">
        <div class="bg-zinc-50 dark:bg-zinc-950 p-6 space-y-6">
            <div>
                <flux:heading size="lg">Stock Log Detail</flux:heading>
                <flux:subheading>Detail historis perubahan stok barang</flux:subheading>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm">
                <flux:heading class="mb-4">Informasi Produk</flux:heading>
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2">
                        <span class="text-xs text-zinc-500 uppercase font-medium">Log ID</span>
                        <span class="text-xs font-mono">{{ $selectedHistory?->id }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-zinc-500 uppercase font-medium">Nama Produk</div>
                            <div class="text-sm font-bold text-green-600">{{ $selectedHistory?->product?->name }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-zinc-500 uppercase font-medium">Merchant</div>
                            <div class="text-sm">{{ $selectedHistory?->merchant?->name ?? 'Sistem' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm">
                <flux:heading class="mb-4">Mutasi Stok</flux:heading>
                <div class="grid grid-cols-3 gap-6 text-center">
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                        <div class="text-xs text-zinc-500 uppercase font-medium mb-1">Sebelum</div>
                        <div class="text-xl font-bold">{{ number_format($selectedHistory?->stock_before) }}</div>
                    </div>
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                        <div class="text-xs text-zinc-500 uppercase font-medium mb-1">Mutasi</div>
                        @php $diff = ($selectedHistory?->stock_after - $selectedHistory?->stock_before); @endphp
                        <div class="text-xl font-bold {{ $diff >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                        </div>
                    </div>
                    <div
                        class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border-2 border-zinc-200 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500 uppercase font-medium mb-1">Akhir</div>
                        <div class="text-xl font-black">{{ number_format($selectedHistory?->stock_after) }}</div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <div class="text-[11px] text-zinc-400 uppercase tracking-widest">Waktu Kejadian</div>
                <div class="text-sm font-medium text-zinc-600 dark:text-zinc-300">
                    {{ $selectedHistory?->created_at?->format('d F Y - H:i:s') }}
                </div>
            </div>

            <div class="flex gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" class="outline outline-2">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

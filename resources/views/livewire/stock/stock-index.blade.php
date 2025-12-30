<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-6">
        <flux:heading size="xl" class="mb-1">Product Stock Overview</flux:heading>
        <flux:subheading>Real-time inventory levels across all products</flux:subheading>
    </div>

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Product Name</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Available</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">In Delivery</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Bad Stock</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Total Stock</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($stocks as $stock)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                        wire:key="{{ $stock->id }}">
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $stock->product->name ?? 'Unknown Product' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <flux:badge color="success" variant="pill" size="sm" class="min-w-[40px]">
                                {{ $stock->stock_available }}
                            </flux:badge>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <flux:badge color="zinc" variant="pill" size="sm" class="min-w-[40px]">
                                {{ $stock->stock_in_delivery }}
                            </flux:badge>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <flux:badge color="danger" variant="pill" size="sm" class="min-w-[40px]">
                                {{ $stock->bad_stock }}
                            </flux:badge>
                        </td>

                        <td class="px-4 py-3 text-center text-sm font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $stock->stock_available + $stock->stock_in_delivery + $stock->bad_stock }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            <flux:button size="sm" variant="ghost" icon="eye"
                                wire:click="showDetail('{{ $stock->id }}')">
                                Details
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-zinc-500">No stock data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $stocks->links() }}
    </div>

    <flux:modal name="stock-detail-modal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Product Details</flux:heading>
                <flux:subheading>{{ $selectedStock?->product->name }}</flux:subheading>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-4 text-center shadow-sm">
                    <div class="text-xs text-zinc-500 uppercase font-medium mb-1">Available for Sale</div>
                    <div class="text-xl font-bold text-green-600">{{ $selectedStock?->stock_available }}</div>
                </div>
                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-4 text-center shadow-sm">
                    <div class="text-xs text-zinc-500 uppercase font-medium mb-1">Currently in Delivery</div>
                    <div class="text-xl font-bold text-blue-600">{{ $selectedStock?->stock_in_delivery }}</div>
                </div>
                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-4 text-center shadow-sm">
                    <div class="text-xs text-zinc-500 uppercase font-medium mb-1">Damaged / Bad Stock</div>
                    <div class="text-xl font-bold text-red-600">{{ $selectedStock?->bad_stock }}</div>
                </div>
            </div>

            <div
                class="mt-4 p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm text-center">
                <div class="text-sm font-bold">Total Physical Inventory</div>
                <div class="text-2xl font-black underline mt-1">
                    {{ ($selectedStock?->stock_available ?? 0) + ($selectedStock?->stock_in_delivery ?? 0) + ($selectedStock?->bad_stock ?? 0) }}
                </div>
            </div>

            <div class="space-y-2">
                <flux:heading size="sm">Stock History</flux:heading>
                <div class="max-h-[250px] overflow-y-auto space-y-2">
                    @forelse ($this->stockLogs as $log)
                        @php
                            $diff = $log->stock_after - $log->stock_before;
                            $isIn = $diff > 0;
                        @endphp
                        <div
                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-3 shadow-sm flex justify-between items-center">
                            <div class="text-left">
                                <div class="font-medium flex items-center gap-2">
                                    {{ $isIn ? 'Stock In' : 'Stock Out' }}
                                    @if ($log->status)
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full {{ $log->status === 'delivering' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-500">{{ $log->created_at->format('d M Y H:i') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-bold {{ $isIn ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                </div>
                                <div class="text-xs text-zinc-500">{{ $log->stock_before }} → {{ $log->stock_after }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-zinc-500 py-4 text-center">No stock history available</div>
                    @endforelse
                </div>

                @if ($this->stockLogs->hasPages())
                    <div class="mt-2">
                        {{ $this->stockLogs->links() }}
                    </div>
                @endif
            </div>

            <div class="flex gap-2 mt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

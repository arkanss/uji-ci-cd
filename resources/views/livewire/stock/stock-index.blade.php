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

            <div class="space-y-4">
                <div class="flex justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <span class="text-sm text-zinc-500">Available for Sale</span>
                    <span class="text-sm font-bold text-green-600">{{ $selectedStock?->stock_available }}</span>
                </div>
                <div class="flex justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <span class="text-sm text-zinc-500">Currently in Delivery</span>
                    <span class="text-sm font-bold text-blue-600">{{ $selectedStock?->stock_in_delivery }}</span>
                </div>
                <div class="flex justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <span class="text-sm text-zinc-500">Damaged / Bad Stock</span>
                    <span class="text-sm font-bold text-red-600">{{ $selectedStock?->bad_stock }}</span>
                </div>
                <hr class="border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-between p-3">
                    <span class="text-sm font-bold">Total Physical Inventory</span>
                    <span class="text-sm font-black underline">
                        {{ ($selectedStock?->stock_available ?? 0) + ($selectedStock?->stock_in_delivery ?? 0) + ($selectedStock?->bad_stock ?? 0) }}
                    </span>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

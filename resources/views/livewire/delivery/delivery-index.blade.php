<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-6">
        <flux:heading size="xl" class="mb-1">Delivery Management</flux:heading>
        <flux:subheading>Monitor and track product distribution deliveries</flux:subheading>
    </div>

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Code</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Driver</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Total Items</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Delivery Date</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Status</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($deliveries as $delivery)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                        wire:key="{{ $delivery->id }}">
                        <td class="px-4 py-3 font-mono text-sm">
                            {{ $delivery->code }}
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                            {{ $delivery->driver->name ?? 'No Driver Assigned' }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-medium">
                            {{ $delivery->distributions->count() }} Items
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $delivery->date->format('d M Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <flux:badge size="sm" variant="outline" class="font-bold"
                                :color="$delivery->status instanceof \App\Enums\ProductDistributionDeliverEnum ? $delivery->status->color() : 'gray'">
                                {{ $delivery->status instanceof \App\Enums\ProductDistributionDeliverEnum ? $delivery->status->label() : $delivery->status }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />

                                <flux:menu>
                                    <flux:menu.item icon="document" wire:click="downloadPdf('{{ $delivery->id }}')">
                                        Download PDF
                                    </flux:menu.item>

                                    <flux:menu.item icon="eye" wire:click="showDetail('{{ $delivery->id }}')">
                                        Detail
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-zinc-500">No delivery data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $deliveries->links() }}
    </div>

    <flux:modal name="delivery-detail-modal" class="w-full max-w-6xl space-y-0 p-0">
        @if ($selectedDelivery)
            <div class="p-6 border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <flux:heading size="xl">{{ $selectedDelivery->code }}</flux:heading>
                        </div>
                        <flux:subheading>{{ $selectedDelivery->date?->format('d M Y, H:i') }}</flux:subheading>
                    </div>

                    <div
                        class="flex items-center gap-3 bg-white dark:bg-zinc-900 p-2 px-3 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
                        <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-full">
                            <flux:icon.truck class="size-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-[10px] uppercase text-zinc-500 font-semibold leading-none">Main Driver</p>
                            <p class="text-sm font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $selectedDelivery->driver->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-8">
                <div
                    class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-zinc-50/50 dark:bg-white/5 rounded-xl border border-zinc-100 dark:border-zinc-800">
                    <div class="flex flex-col gap-1.5">
                        <flux:label class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Delivery Date
                        </flux:label>
                        <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                            {{ $selectedDelivery->date?->format('d F Y') }}</p>
                    </div>

                    <div class="flex flex-col gap-1.5 md:border-l md:pl-4 border-zinc-200/50 dark:border-zinc-700/50">
                        <flux:label class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Total PO
                        </flux:label>
                        <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                            {{ count($selectedDelivery->distributions) }} PO</p>
                    </div>

                    <div class="flex flex-col gap-1.5 md:border-l md:pl-4 border-zinc-200/50 dark:border-zinc-700/50">
                        <flux:label class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Delivery
                            Status</flux:label>
                        <div class="flex items-center mt-1">
                            <flux:badge :color="$selectedDelivery->status?->color() ?? 'zinc'" variant="solid"
                                size="sm" class="uppercase font-bold text-[9px]">
                                {{ $selectedDelivery->status?->label() ?? 'Pending' }}
                            </flux:badge>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5 md:border-l md:pl-4 border-zinc-200/50 dark:border-zinc-700/50">
                        <flux:label class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Verified By
                        </flux:label>
                        <div class="mt-1">
                            <flux:badge color="blue" variant="subtle" size="sm" icon="check-badge"
                                class="font-bold uppercase text-[9px]">
                                {{ $selectedDelivery->distributions->first()?->verifier?->name ?? 'System' }}
                            </flux:badge>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between text-zinc-500">
                        <div class="flex items-center gap-2">
                            <flux:icon.clipboard-document-list class="size-4" />
                            <h4 class="text-xs font-bold uppercase tracking-widest">Ordered Products Distribution</h4>
                        </div>
                        @if ($distributions && $distributions->hasPages())
                            <span class="text-[10px] bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded-full">
                                Page {{ $distributions->currentPage() }} of {{ $distributions->lastPage() }}
                            </span>
                        @endif
                    </div>

                    @forelse ($distributions ?? [] as $dist)
                        <div
                            class="border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-zinc-900">
                            <div
                                class="px-4 py-3 bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $dist->code }}</span>
                                </div>
                                <flux:badge size="sm" variant="subtle" :color="$dist->status?->color() ?? 'zinc'"
                                    class="text-[9px] font-bold uppercase">
                                    {{ $dist->status?->label() ?? '-' }}
                                </flux:badge>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead
                                        class="bg-zinc-50/50 dark:bg-zinc-800/30 text-[10px] uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                                        <tr>
                                            <th class="px-4 py-2 font-bold">Product</th>
                                            <th class="px-4 py-2 font-bold text-center">Reqested Qty</th>
                                            <th class="px-4 py-2 font-bold text-center">Approved Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50 text-sm">
                                        @foreach ($dist->items ?? [] as $item)
                                            <tr class="hover:bg-zinc-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                                                <td class="px-4 py-2.5">
                                                    <div class="font-medium text-zinc-800 dark:text-zinc-200">
                                                        {{ $item->product?->name }}</div>
                                                    <div class="text-[10px] text-zinc-500 font-mono">
                                                        {{ $item->product?->sku }}</div>
                                                </td>
                                                <td
                                                    class="px-4 py-2.5 text-center text-zinc-600 dark:text-zinc-400 font-mono font-bold">
                                                    {{ $item->requested_stock }}
                                                </td>
                                                <td class="px-4 py-2.5 text-center">
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $item->approved_stock > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20' : 'bg-zinc-100 text-zinc-400' }}">
                                                        {{ $item->approved_stock ?? 0 }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div
                            class="py-8 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl">
                            <p class="text-zinc-500 text-sm italic">No items available.</p>
                        </div>
                    @endforelse

                    @if ($distributions && $distributions->hasPages())
                        <div class="mt-4 flex justify-center">
                            {{ $distributions->links(data: ['scrollTo' => false]) }}
                        </div>
                    @endif
                </div>

                <div
                    class="flex flex-col items-center justify-center p-6 border-t border-zinc-100 dark:border-zinc-800 space-y-3">
                    <div class="bg-white p-3 rounded-xl border border-zinc-200 shadow-sm">
                        @if ($selectedDelivery)
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode('/delivery/' . $selectedDelivery->id) }}" 
                                alt="QR Code Delivery" class="w-32 h-32" />
                        @endif
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-bold uppercase tracking-widest text-zinc-800 dark:text-zinc-200">Scan to
                            Verify Delivery</p>
                    </div>
                </div>
            </div>

            <div
                class="p-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-3 rounded-b-lg">
                <flux:modal.close>
                    <flux:button variant="ghost" size="sm">Close</flux:button>
                </flux:modal.close>
            </div>
        @endif
    </flux:modal>
</div>

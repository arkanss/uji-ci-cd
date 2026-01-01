<div>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl" class="mb-1">Receive Purchase Orders</flux:heading>
                <flux:subheading>Terima barang sesuai PO (tampilkan PO berstatus Requested dan Completed).
                </flux:subheading>
            </div>
        </div>

        <div class="space-y-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari kode PO..."
                class="max-w-sm" />

            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                        <tr>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">PO
                                Number</th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Created
                                at</th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Status
                            </th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">
                                Completed By</th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">
                                Completed At</th>
                            <th
                                class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase text-right">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($purchaseOrders as $po)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                                wire:key="{{ $po->id }}">
                                <td class="px-4 py-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $po->po_number }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $po->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    @if ($po->status == 1)
                                        <flux:badge color="yellow" size="sm">Requested</flux:badge>
                                    @elseif($po->status == 2)
                                        <flux:badge color="green" size="sm">Completed</flux:badge>
                                    @else
                                        <flux:badge size="sm">Unknown</flux:badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-600">
                                    {{ $po->completedBy?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $po->completed_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end">
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                inset="top bottom" />
                                            <flux:menu>
                                                @if ($po->status == 2)
                                                    <flux:menu.item icon="document-text"
                                                        wire:click="showDetail('{{ $po->id }}')">Detail
                                                    </flux:menu.item>
                                                @else
                                                    <flux:menu.item icon="arrow-down-tray"
                                                        wire:click="showReceive('{{ $po->id }}')">Receive
                                                    </flux:menu.item>
                                                @endif
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-zinc-500">No purchase orders to
                                    receive</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $purchaseOrders->links() }}</div>
        </div>

        <flux:modal name="receive-po-modal" class="w-full max-w-6xl space-y-0 p-0">
            @if ($selectedPO)
                <div>
                    <flux:heading size="lg">{{ $viewOnly ? 'Purchase Order Detail' : 'Receive Purchase Order' }}
                    </flux:heading>
                    <div class="mt-1 text-sm text-zinc-500">#{{ $selectedPO->po_number }} • Created
                        {{ $selectedPO->created_at->format('d M Y, H:i') }}
                        @if ($selectedPO->status == 2)
                            • <span class="text-green-600">Completed</span>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto mt-6 mb-6">
                    <table class="w-full text-left border-collapse">
                        <thead
                            class="bg-zinc-50/50 dark:bg-zinc-800/30 
                                text-[10px] uppercase tracking-wider text-zinc-500 
                                border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-4 py-2 font-bold">Destination Warehouse</th>
                                <th class="px-4 py-2 font-bold">Requested By</th>
                                <th class="px-4 py-2 font-bold">Status</th>
                                <th class="px-4 py-2 font-bold">Completed By</th>
                                <th class="px-4 py-2 font-bold">Completed At</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50 text-sm">
                            <tr class="hover:bg-zinc-50/30 dark:hover:bg-zinc-800/20 transition-colors">

                                <td class="px-4 py-2.5 font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ \DB::table('warehouse_addresses')->where('id', $selectedPO->warehouse_id)->value('name') ?? '-' }}
                                </td>

                                <td class="px-4 py-2.5 font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $selectedPO->createdBy?->name ?? '-' }}
                                </td>

                                <td class="px-4 py-2.5">
                                    @if ($selectedPO->status == 1)
                                        <flux:badge color="yellow">Requested</flux:badge>
                                    @elseif ($selectedPO->status == 2)
                                        <flux:badge color="green">Completed</flux:badge>
                                    @else
                                        <flux:badge>Unknown</flux:badge>
                                    @endif
                                </td>

                                <td class="px-4 py-2.5 font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $selectedPO->completedBy?->name ?? '-' }}
                                </td>

                                <td class="px-4 py-2.5 font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $selectedPO->completed_at?->format('d M Y, H:i') ?? '-' }}
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>


                <div x-data="{ showAll: false }">
                    <flux:heading size="sm">Items</flux:heading>
                    <div class="mt-2 text-sm text-zinc-500">Total items: {{ count($receiveItems) }}</div>

                    <div class="mt-3">
                        <div class="overflow-hidden border rounded bg-white dark:bg-zinc-900">
                            <div :class="showAll ? 'max-h-full' : 'max-h-64'" class="overflow-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-2 text-xs text-zinc-500">#</th>
                                            <th class="px-4 py-2 text-xs text-zinc-500">Product</th>
                                            <th class="px-4 py-2 text-xs text-zinc-500">Requested</th>
                                            <th class="px-4 py-2 text-xs text-zinc-500">Received</th>
                                            <th class="px-4 py-2 text-xs text-zinc-500">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                        @foreach ($receiveItems as $idx => $it)
                                            <tr wire:key="item-{{ $it['id'] }}">
                                                <td class="px-4 py-2 text-sm text-zinc-600">{{ $idx + 1 }}</td>
                                                <td class="px-4 py-2 text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $it['product_name'] ?? '-' }}</td>
                                                <td class="px-4 py-2 text-sm text-zinc-700 text-right">
                                                    {{ $it['requested_stock'] }}</td>
                                                <td class="px-4 py-2">
                                                    @if ($viewOnly)
                                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                            {{ $it['received_stock'] ?? '-' }}</div>
                                                    @else
                                                        <flux:input size="sm" type="number" class="w-28"
                                                            wire:model.defer="receiveItems.{{ $idx }}.received_stock" />
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2 text-sm text-zinc-600">
                                                    {{ $it['unit_name'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-2">
                            <button x-show="!showAll" @click="showAll = true" type="button"
                                class="text-sm text-zinc-600 hover:underline">Show all items</button>
                            <button x-show="showAll" @click="showAll = false" type="button"
                                class="text-sm text-zinc-600 hover:underline">Collapse</button>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 pt-4">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Close</flux:button>
                    </flux:modal.close>
                    @unless ($viewOnly)
                        <flux:button wire:click="saveReceive" variant="primary">Save Receive</flux:button>
                    @endunless
                </div>
            @endif
        </flux:modal>

    </div>

    @if (session('po_success'))
        <script>
            alert(@json(session('po_success')));
        </script>
    @endif

    @if (session('po_error'))
        <script>
            alert(@json(session('po_error')));
        </script>
    @endif
</div>

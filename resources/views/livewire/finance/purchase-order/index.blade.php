<div>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl" class="mb-1">Purchase Orders</flux:heading>
                <flux:subheading>Kelola distribusi produk dan status pemesanan.</flux:subheading>
            </div>

            <a href="{{ route('finance.purchase-order.create') }}">
                <flux:button variant="primary" icon="plus">
                    Add Purchase Order
                </flux:button>
            </a>
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
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Vendor
                            </th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Grand
                                Total</th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Created
                                By</th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Status
                            </th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Created
                                at</th>
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
                                    {{ $po->po_number }}
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $po->vendor?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $po->currency }} {{ number_format($po->grand_total ?? 0, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $users[$po->created_by]->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <flux:badge color="{{ $po->status->color() }}" size="sm">
                                        {{ $po->status->label() }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $po->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end items-center gap-1">

                                        @if ($po->status === \App\Enums\PurchaseOrderStatusEnum::Requested)
                                            <flux:button variant="ghost" size="sm" icon="check-circle"
                                                wire:click="openApproval('{{ $po->id }}')">
                                                Approval
                                            </flux:button>
                                        @endif

                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                inset="top bottom" />

                                            <flux:menu>

                                                <flux:menu.item icon="arrow-down-tray"
                                                    wire:click="download('{{ $po->id }}')">
                                                    Download
                                                </flux:menu.item>

                                                <flux:menu.item icon="eye"
                                                    wire:click="showDetail('{{ $po->id }}')">
                                                    Detail
                                                </flux:menu.item>

                                                @if ($po->status != 2)
                                                    <a href="{{ route('finance.purchase-order.edit', $po->id) }}">
                                                        <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                                    </a>

                                                    <flux:menu.separator />

                                                    <flux:menu.item icon="trash" variant="danger"
                                                        wire:click="confirmDelete('{{ $po->id }}')">
                                                        Delete
                                                    </flux:menu.item>
                                                @else
                                                    <flux:menu.separator />
                                                    <flux:menu.item disabled>No actions</flux:menu.item>
                                                @endif
                                            </flux:menu>
                                        </flux:dropdown>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-zinc-500">No purchase orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $purchaseOrders->links() }}</div>
        </div>

        <flux:modal name="delete-po-modal" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Delete Purchase Order?</flux:heading>
                    <flux:text class="mt-2">
                        You're about to delete this purchase order. This action cannot be reversed.
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button wire:click="deleteConfirmed" variant="danger">Delete</flux:button>
                </div>
            </div>
        </flux:modal>
    </div>

    @if (session('po_success'))
        <script>
            alert('{{ session('po_success') }}');
        </script>
    @endif

    @if (session('po_error'))
        <script>
            alert('{{ session('po_error') }}');
        </script>
    @endif

    <flux:modal name="detail-po-modal" class="w-full max-w-6xl space-y-0 p-0">
        @if ($selectedPO)
            <div class="space-y-6">
                <div>
                    <div class="flex items-center gap-3">
                        <flux:heading size="lg">Purchase Order</flux:heading>
                        <div class="text-sm text-zinc-700 dark:text-zinc-300">#{{ $selectedPO['po']->po_number }}
                        </div>
                    </div>
                    <div class="mt-1 text-sm text-zinc-500">Created
                        {{ $selectedPO['po']->created_at->format('d M Y, H:i') }}
                    </div>
                </div>

                <div class="overflow-x-auto mt-3">
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
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50 text-sm">
                            <tr class="hover:bg-zinc-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                                <td class="px-4 py-2.5 font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $selectedPO['warehouse'] ?? '-' }}
                                </td>

                                <td class="px-4 py-2.5 font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $selectedPO['createdBy'] ?? '-' }}
                                </td>

                                <td class="px-4 py-2.5">
                                    <flux:badge color="{{ $po->status->color() }}">
                                        {{ $po->status->label() }}
                                    </flux:badge>
                                </td>

                                <td class="px-4 py-2.5 font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $selectedPO['completedBy'] ?? '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <flux:heading size="sm">Items</flux:heading>
                    <div
                        class="overflow-x-auto bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded mt-2">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                                <tr>
                                    <th class="px-4 py-2 text-xs text-zinc-500">No</th>
                                    <th class="px-4 py-2 text-xs text-zinc-500">Product</th>
                                    <th class="px-4 py-2 text-xs text-zinc-500">Unit</th>
                                    <th class="px-4 py-2 text-xs text-zinc-500 text-right">Requested</th>
                                    <th class="px-4 py-2 text-xs text-zinc-500 text-right">Received</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($selectedPO['items'] as $idx => $it)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $it->product_name ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $it->unit_name ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-right">{{ $it->requested_stock }}</td>
                                        <td class="px-4 py-2 text-sm text-right">{{ $it->received_stock ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-4">

                    @if ($approvalMode && $selectedPO['po']->status === \App\Enums\PurchaseOrderStatusEnum::Requested)
                        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800">
                            <flux:textarea label="Reject Reason (wajib jika reject)" wire:model.defer="rejectReason"
                                placeholder="Masukkan alasan penolakan..." rows="3" class="w-full" />

                            @error('rejectReason')
                                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="ghost">Close</flux:button>
                        </flux:modal.close>

                        @if ($approvalMode && $selectedPO['po']->status === \App\Enums\PurchaseOrderStatusEnum::Requested)
                            <flux:button variant="danger" wire:click="rejectPO">
                                Reject
                            </flux:button>

                            <flux:button variant="primary" wire:click="approvePO">
                                Approve
                            </flux:button>
                        @endif

                    </div>

                </div>

            </div>
        @endif
    </flux:modal>

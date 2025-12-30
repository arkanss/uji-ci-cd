<div>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl" class="mb-1">Purchase Orders</flux:heading>
                <flux:subheading>Kelola distribusi produk dan status pemesanan.</flux:subheading>
            </div>

            <flux:button variant="primary" icon="plus" wire:click="create">
                Add PO
            </flux:button>
        </div>

        <div class="space-y-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari kode PO..."
                class="max-w-sm" />

            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                        <tr>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">PO Number</th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Created By</th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Created at</th>
                            <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($purchaseOrders as $po)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                                wire:key="{{ $po->id }}">
                                <td class="px-4 py-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $po->po_number }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $users[$po->created_by]->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if ($po->status == 1)
                                        <flux:badge color="yellow" size="sm">Requested</flux:badge>
                                    @elseif($po->status == 2)
                                        <flux:badge color="green" size="sm">Completed</flux:badge>
                                    @else
                                        <flux:badge size="sm">Unknown</flux:badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $po->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end">
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                inset="top bottom" />

                                            <flux:menu>
                                                <flux:menu.item icon="eye"
                                                    wire:click="showDetail('{{ $po->id }}')">Detail
                                                </flux:menu.item>
                                                @if ($po->status != 2)
                                                    <flux:menu.item icon="pencil-square"
                                                        wire:click="edit('{{ $po->id }}')">Edit</flux:menu.item>

                                                    <flux:menu.separator />

                                                    <flux:menu.item icon="trash" variant="danger"
                                                        wire:click="confirmDelete('{{ $po->id }}')">Delete
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
                                <td colspan="5" class="px-4 py-12 text-center text-zinc-500">No purchase orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $purchaseOrders->links() }}</div>
        </div>

        <flux:modal name="po-modal" class="md:w-[900px] space-y-6">
            <form wire:submit.prevent="save" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $selectedId ? 'Update Purchase Order' : 'Create Purchase Order' }}
                    </flux:heading>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Date</flux:label>
                        <flux:input type="text" wire:model="date" disabled />
                    </flux:field>

                    <flux:field>
                        <flux:label>Destination Warehouse</flux:label>
                        <flux:select wire:model="warehouse_id" placeholder="Select warehouse">
                            <option value="">-- Select Destination Warehouse --</option>
                            @foreach ($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Notes</flux:label>
                        <flux:input wire:model="notes" />
                    </flux:field>
                </div>

                <div>
                    <flux:heading size="sm" class="mb-2">Items</flux:heading>
                    <div class="space-y-3">
                        <div class="max-h-[55vh] overflow-auto pr-2 space-y-3">
                            @foreach ($items as $index => $item)
                                <div class="p-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                                        <div class="md:col-span-6">
                                            <flux:field>
                                                <flux:label>Product</flux:label>
                                                <flux:select wire:model="items.{{ $index }}.product_id">
                                                    <option value="">-- Select Product --</option>
                                                    @foreach ($products as $p)
                                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                    @endforeach
                                                </flux:select>
                                            </flux:field>
                                        </div>
                                        <div class="md:col-span-3">
                                            <flux:field>
                                                <flux:label>Requested</flux:label>
                                                <flux:input type="number"
                                                    wire:model="items.{{ $index }}.requested_stock" />
                                            </flux:field>
                                        </div>
                                        <div class="md:col-span-2">
                                            <flux:field>
                                                <flux:label>Unit</flux:label>
                                                <flux:select wire:model="items.{{ $index }}.unit_id">
                                                    <option value="">-- Select Unit --</option>
                                                    @if (isset($units) && $units->isNotEmpty())
                                                        @foreach ($units as $u)
                                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                        @endforeach
                                                    @else
                                                        <option value="Box">Box</option>
                                                        <option value="Carton">Carton</option>
                                                        <option value="Dozen">Dozen</option>
                                                        <option value="Pack">Pack</option>
                                                        <option value="Pieces">Pieces</option>
                                                    @endif
                                                </flux:select>
                                            </flux:field>
                                        </div>
                                        <div class="md:col-span-1 text-right pr-2">
                                            <flux:button variant="danger" size="sm"
                                                wire:click.prevent="removeItem({{ $index }})">Remove</flux:button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <flux:button variant="primary" wire:click.prevent="addItem">Add Item</flux:button>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 pt-4">
                    <flux:button type="submit" variant="primary">Save</flux:button>
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                </div>
            </form>
        </flux:modal>

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
            alert(@json(session('po_success')));
        </script>
    @endif

    @if (session('po_error'))
        <script>
            alert(@json(session('po_error')));
        </script>
    @endif

    <flux:modal name="detail-po-modal" class="md:w-[700px]">
        @if ($selectedPO)
            <div class="space-y-6">
                <div>
                    <div class="flex items-center gap-3">
                        <flux:heading size="lg">Purchase Order</flux:heading>
                        <div class="text-sm text-zinc-700 dark:text-zinc-300">#{{ $selectedPO['po']->po_number }}
                        </div>
                    </div>
                    <div class="mt-1 text-sm text-zinc-500">Created
                        {{ $selectedPO['po']->created_at->format('d M Y, H:i') }}</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded mt-3">
                    <div>
                        <div class="text-zinc-500 text-xs">Destination Warehouse</div>
                        <div class="text-zinc-900 dark:text-zinc-100 font-medium">
                            {{ $selectedPO['warehouse'] ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-zinc-500 text-xs">Requested By</div>
                        <div class="text-zinc-900 dark:text-zinc-100 font-medium">
                            {{ $selectedPO['createdBy'] ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-zinc-500 text-xs">Status</div>
                        <div class="mt-1">
                            @if ($selectedPO['po']->status == 1)
                                <flux:badge color="yellow">Requested</flux:badge>
                            @elseif($selectedPO['po']->status == 2)
                                <flux:badge color="green">Completed</flux:badge>
                            @else
                                <flux:badge>Unknown</flux:badge>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-zinc-500 text-xs">Completed By</div>
                        <div class="text-zinc-900 dark:text-zinc-100">{{ $selectedPO['completedBy'] ?? '-' }}</div>
                    </div>
                </div>

                @if ($selectedPO['po']->notes)
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 p-4 rounded">
                        <div class="text-zinc-500 text-xs">Notes</div>
                        <div class="text-zinc-900 dark:text-zinc-100 mt-1">{{ $selectedPO['po']->notes }}</div>
                    </div>
                @endif

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

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Close</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
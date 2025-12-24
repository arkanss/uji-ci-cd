<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-6">
        <flux:heading size="xl" class="mb-1">Purchase Orders</flux:heading>
        <flux:subheading>Monitor, verify, and track purchase order status.</flux:subheading>
    </div>

    <div class="space-y-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search orders..."
            class="max-w-sm" />

        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Code</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Order Status</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Payment</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Requester</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Verified By</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            wire:key="{{ $order->id }}">
                            <td class="px-4 py-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $order->code }}
                            </td>

                            <td class="px-4 py-3 text-sm">
                                <flux:badge :color="$order->status?->color() ?? 'zinc'" variant="subtle">
                                    {{ $order->status?->label() ?? 'Unknown' }}
                                </flux:badge>
                            </td>

                            <td class="px-4 py-3 text-sm">
                                @php $latestPayment = $order->latestPayment(); @endphp
                                @if ($latestPayment && $latestPayment->status)
                                    <flux:badge :color="$latestPayment->status?->color() ?? 'zinc'" variant="outline"
                                        size="sm">
                                        {{ $latestPayment->status?->label() ?? 'Pending' }}
                                    </flux:badge>
                                @else
                                    <flux:badge color="zinc" variant="outline" size="sm">No Payment</flux:badge>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm">
                                @if ($order->requester)
                                    <flux:badge icon="user-circle" color="indigo" variant="subtle" size="sm">
                                        {{ $order->requester->name }}
                                    </flux:badge>
                                @else
                                    <flux:badge icon="cpu-chip" color="amber" variant="subtle" size="sm">
                                        System
                                    </flux:badge>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm">
                                @if ($order->verifier)
                                    <flux:badge icon="check-badge" color="blue" variant="subtle" size="sm">
                                        {{ $order->verifier->name }}
                                    </flux:badge>
                                @else
                                    <span class="text-zinc-400 italic text-xs">Unverified</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right">
                                <flux:button variant="ghost" size="sm" icon="eye"
                                    wire:click="showDetail('{{ $order->id }}')" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-zinc-500">Belum ada request pesanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>

    <flux:modal name="detail-modal" class="md:w-[700px] space-y-0 p-0">
        @if ($selectedOrder)
            <div class="p-6 border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <flux:heading size="xl">{{ $selectedOrder->code }}</flux:heading>

                            @php $modalPayment = $selectedOrder->latestPayment(); @endphp
                            @if ($modalPayment && $modalPayment->status)
                                <flux:badge :color="$modalPayment->status?->color() ?? 'zinc'" variant="outline"
                                    size="sm">
                                    {{ $modalPayment->status?->label() ?? 'Pending' }}
                                </flux:badge>
                            @else
                                <flux:badge color="zinc" variant="outline" size="sm">No Payment</flux:badge>
                            @endif
                        </div>
                        <flux:subheading>Dibuat pada
                            {{ \Carbon\Carbon::parse($selectedOrder->created_at)->format('d M Y, H:i') }}
                        </flux:subheading>
                    </div>

                    <div
                        class="flex items-center gap-3 bg-white dark:bg-zinc-900 p-2 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
                        <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-full">
                            <flux:icon.user class="size-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div class="pr-2">
                            <p class="text-[10px] uppercase text-zinc-500 font-semibold leading-none">Requester</p>
                            <p class="text-sm font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $selectedOrder->requester->name ?? 'System' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-8">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label class="text-[11px] uppercase tracking-wider">Verified By</flux:label>
                        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ $selectedOrder->verifier->name ?? '-' }}
                        </p>
                    </flux:field>

                    <flux:field>
                        <flux:label class="text-[11px] uppercase tracking-wider">Order Status</flux:label>
                        <div>
                            <flux:badge :color="$selectedOrder->status?->color() ?? 'zinc'" size="sm"
                                variant="subtle">
                                {{ $selectedOrder->status?->label() ?? 'Unknown' }}
                            </flux:badge>
                        </div>
                    </flux:field>

                    <flux:field class="col-span-2 md:col-span-1">
                        <flux:label class="text-[11px] uppercase tracking-wider">Items Count</flux:label>
                        <p class="text-sm font-medium">{{ $selectedOrder->items->count() }} Produk</p>
                    </flux:field>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <flux:icon.shopping-bag class="size-4 text-zinc-400" />
                        <h4 class="text-xs font-bold uppercase tracking-widest text-zinc-500">Daftar Item</h4>
                    </div>
                    <div class="border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-700">
                                <tr>
                                    <th class="px-4 py-2.5 text-[11px] font-bold text-zinc-500 uppercase">Produk</th>
                                    <th class="px-4 py-2.5 text-[11px] font-bold text-zinc-500 uppercase text-center">
                                        Req Qty</th>
                                    <th class="px-4 py-2.5 text-[11px] font-bold text-zinc-500 uppercase text-center">
                                        Appr Qty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($selectedOrder->items as $item)
                                    <tr wire:key="item-row-{{ $item->id }}"
                                        class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                {{ $item->product->name ?? 'Produk Tidak Ditemukan' }}
                                            </div>
                                            <div class="text-[10px] text-zinc-500 font-mono">
                                                SKU: {{ $item->product->sku ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                {{ $item->requested_stock }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($selectedOrder->status === \App\Enums\OrderRequestEnum::Requested)
                                                <span class="text-zinc-400 italic text-xs">Waiting...</span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                                    {{ $item->approved_stock ?? 0 }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($selectedOrder->status === \App\Enums\OrderRequestEnum::Rejected)
                    <div class="p-4 bg-red-50 dark:bg-red-900/10 border-l-4 border-red-500 rounded-r-lg">
                        <div class="flex items-center gap-2 mb-1 text-red-700 dark:text-red-400">
                            <flux:icon.exclamation-triangle class="size-4" />
                            <span class="text-xs font-bold uppercase tracking-wider">Alasan Penolakan</span>
                        </div>
                        <p class="text-sm text-red-600 dark:text-red-300 leading-relaxed italic">
                            "{{ $selectedOrder->reject_reason ?? 'Tidak ada alasan spesifik.' }}"
                        </p>
                    </div>
                @endif
            </div>

            <div
                class="p-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-3 rounded-b-lg">
                <flux:modal.close>
                    <flux:button variant="ghost" size="sm">Tutup</flux:button>
                </flux:modal.close>

                @if ($selectedOrder->status === \App\Enums\OrderRequestEnum::Requested)
                    <flux:modal.trigger name="reject-modal">
                        <flux:button variant="danger" size="sm" icon="x-mark">Tolak</flux:button>
                    </flux:modal.trigger>

                    <flux:button variant="primary" size="sm" icon="check"
                        wire:click="verifyOrder('{{ $selectedOrder->id }}')"
                        wire:confirm="Setujui dan verifikasi pesanan ini?">
                        Verifikasi Pesanan
                    </flux:button>
                @endif
            </div>
        @endif
    </flux:modal>

    <flux:modal name="reject-modal" class="md:w-[400px]">
        <div class="space-y-4">
            <div>
                <flux:heading>Alasan Penolakan</flux:heading>
                <flux:subheading>Berikan penjelasan singkat kenapa pesanan ini ditolak.</flux:subheading>
            </div>

            <flux:textarea wire:model="rejectReason" placeholder="Contoh: Stok barang di gudang sedang kosong..." />
            <flux:error name="rejectReason" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="rejectOrder('{{ $selectedOrder->id ?? '' }}')">Konfirmasi
                    Tolak</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

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
                        <td class="px-4 py-3 text-sm font-mono font-bold text-green-600 dark:text-green-500">
                            {{ $delivery->code }}
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                            {{ $delivery->driver->name ?? 'No Driver Assigned' }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-medium">
                            {{ $delivery->distributions->count() }} Items
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $delivery->date->format('d/M/Y, H:i') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <flux:badge size="sm" variant="outline" class="font-bold">
                                {{ $delivery->status->name ?? $delivery->status }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button size="sm" variant="ghost" icon="eye"
                                wire:click="showDetail('{{ $delivery->id }}')">
                                View Detail
                            </flux:button>
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

    <flux:modal name="delivery-detail-modal" class="md:w-[900px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    Delivery Detail — {{ $selectedDelivery?->code }}
                </flux:heading>
                <flux:subheading>
                    {{ $selectedDelivery?->date?->format('d M Y, H:i') }}
                </flux:subheading>
            </div>

            <div class="border border-zinc-200 dark:border-zinc-800 rounded-lg p-4 space-y-3">
                <p class="text-xs font-semibold uppercase text-zinc-500">
                    Delivery Information
                </p>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-md p-3">
                        <p class="text-xs text-zinc-500 mb-1">Delivery Code</p>
                        <p class="font-mono font-semibold">
                            {{ $selectedDelivery?->code }}
                        </p>
                    </div>

                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-md p-3">
                        <p class="text-xs text-zinc-500 mb-1">Status</p>
                        <flux:badge size="sm" variant="outline">
                            {{ $selectedDelivery?->status->name ?? '-' }}
                        </flux:badge>
                    </div>

                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-md p-3">
                        <p class="text-xs text-zinc-500 mb-1">Driver</p>
                        <p class="font-medium">
                            {{ $selectedDelivery?->driver->name ?? '-' }}
                        </p>
                    </div>

                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-md p-3">
                        <p class="text-xs text-zinc-500 mb-1">Delivery Date</p>
                        <p class="font-medium">
                            {{ $selectedDelivery?->date?->format('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-800">
                    <p class="text-xs font-semibold uppercase text-zinc-500">
                        Purchase Order Information
                    </p>
                </div>

                <table class="w-full text-sm">
                    <thead class="bg-zinc-100 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-2 text-left">PO Code</th>
                            <th class="px-4 py-2 text-left">Requested By</th>
                            <th class="px-4 py-2 text-left">Verified By</th>
                            <th class="px-4 py-2 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody class="space-y-2">
                        @forelse ($selectedDelivery?->distributions ?? [] as $dist)
                            <tr class="border-t border-zinc-100 dark:border-zinc-800">
                                <td class="px-4 py-3">
                                    <div
                                        class="border border-zinc-200 dark:border-zinc-700 rounded-md p-2 font-mono font-semibold">
                                        {{ $dist->code }}
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-md p-2">
                                        {{ $dist->requester?->name ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-md p-2">
                                        {{ $dist->verifier?->name ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div
                                        class="border border-zinc-200 dark:border-zinc-700 rounded-md p-2 inline-block">
                                        <flux:badge size="sm" variant="outline">
                                            {{ $dist->status->name ?? '-' }}
                                        </flux:badge>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-zinc-500">
                                    No purchase order found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($selectedDelivery)
                <div class="flex justify-center">
                    <div class="p-4 bg-white dark:bg-zinc-900 rounded-lg border shadow-sm text-center space-y-2">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Scan untuk Verifikasi Delivery
                        </p>

                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $selectedDelivery->id }}"
                            alt="QR Code Delivery" class="mx-auto" />

                        <p class="text-[10px] text-zinc-400 font-mono break-all">
                            {{ $selectedDelivery->id }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

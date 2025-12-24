<div class="p-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-1">Delivery Management</flux:heading>
        <flux:subheading>Monitor and track product distribution deliveries</flux:subheading>
    </div>

    {{-- Main Table --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
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
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors" wire:key="{{ $delivery->id }}">
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
                            <flux:button size="sm" variant="ghost" icon="eye" wire:click="showDetail('{{ $delivery->id }}')">
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

    {{-- DETAIL MODAL --}}
    <flux:modal name="delivery-detail-modal" class="md:w-[800px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delivery Detail: {{ $selectedDelivery?->code }}</flux:heading>
                <flux:subheading>Driver: {{ $selectedDelivery?->driver->name ?? '-' }} | Date: {{ $selectedDelivery?->date->format('d M Y') }}</flux:subheading>
            </div>

            <div class="max-h-[400px] overflow-y-auto border border-zinc-200 dark:border-zinc-800 rounded-lg">
                <table class="w-full text-left text-sm">
                    <thead class="sticky top-0 bg-zinc-100 dark:bg-zinc-800 font-medium">
                        <tr>
                            <th class="px-4 py-2 border-b">Merchant</th>
                            <th class="px-4 py-2 border-b">Product</th>
                            <th class="px-4 py-2 border-b text-center">Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @if($selectedDelivery)
                            @foreach ($selectedDelivery->distributions as $dist)
                                <tr>
                                    <td class="px-4 py-2 text-zinc-900 dark:text-zinc-100">
                                        {{ $dist->merchant->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2 text-zinc-600 dark:text-zinc-400">
                                        {{ $dist->product->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2 text-center font-bold">
                                        {{ $dist->quantity ?? 0 }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
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
<div class="max-w-6xl mx-auto p-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Edit DP Item Request</flux:heading>
            <flux:subheading class="mt-1">
                Code: <span class="font-medium">{{ $request->code }}</span>
            </flux:subheading>
        </div>
    </div>

    {{-- BASIC INFO --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-800
                bg-white dark:bg-zinc-900 p-5">
        <flux:heading size="sm">DP Item Request Detail</flux:heading>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-sm text-zinc-500">Request Code</div>
                <div class="font-medium mt-1">{{ $request->code }}</div>
            </div>

            <div>
                <div class="text-sm text-zinc-500">Requested By</div>
                <div class="font-medium mt-1">
                    {{ $request->user?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-sm text-zinc-500">Requested At</div>
                <div class="font-medium mt-1">
                    {{ $request->created_at?->format('d M Y H:i') ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    {{-- STATUS INFO --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-800
                bg-white dark:bg-zinc-900 p-5">
        <flux:heading size="sm">DP Item Request Status</flux:heading>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-zinc-500">Opened At</div>
                <div class="font-medium mt-1">
                    {{ $request->opened_at?->format('d M Y H:i') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-sm text-zinc-500">Opened By</div>
                <div class="font-medium mt-1">
                    {{ $request->openedBy?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-sm text-zinc-500">Closed At</div>
                <div class="font-medium mt-1">
                    {{ $request->closed_at?->format('d M Y H:i') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-sm text-zinc-500">Closed By</div>
                <div class="font-medium mt-1">
                    {{ $request->closedBy?->name ?? '-' }}
                </div>
            </div>

            {{-- STATUS EDIT --}}
            <div class="md:col-span-2">
                <div class="text-sm text-zinc-500 mb-1">Status</div>
                <select
                    wire:model="status"
                    class="w-full rounded-md border border-zinc-300 dark:border-zinc-700
                           bg-white dark:bg-zinc-900 px-3 py-2 text-sm">
                    <option value="1">Requested</option>
                    <option value="2">Processed</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ITEMS --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-800
                bg-white dark:bg-zinc-900 p-5">
        <flux:heading size="sm">Requested Items</flux:heading>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700 text-zinc-500">
                    <tr>
                        <th class="text-left py-2">Product</th>
                        <th class="text-center py-2">Requested</th>
                        <th class="text-center py-2">Received</th>
                        <th class="text-center py-2">Returned</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($request->items as $item)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="py-3">
                                {{ $item->product?->name ?? '-' }}
                            </td>

                            <td class="text-center">
                                {{ $item->requested_stock }}
                            </td>

                            <td class="text-center">
                                <input
                                    type="number"
                                    min="0"
                                    max="{{ $item->requested_stock }}"
                                    wire:model.defer="receivedStock.{{ $item->id }}"
                                    class="w-20 rounded-md border border-zinc-300 dark:border-zinc-700
                                           bg-transparent text-center focus:ring
                                           focus:ring-primary-500/20">
                            </td>

                            <td class="text-center">
                                {{ $item->returned_stock ?? '-' }}
                            </td>
                        </tr>

                        @error('receivedStock.' . $item->id)
                            <tr>
                                <td colspan="4" class="text-sm text-red-500 py-1">
                                    {{ $message }}
                                </td>
                            </tr>
                        @enderror
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-between items-center">
        <a href="{{ route('dp-item-requests.show', $request->id) }}">
            <flux:button variant="ghost">Cancel</flux:button>
        </a>

        <flux:button wire:click="save" variant="primary">
            Save Changes
        </flux:button>
    </div>

</div>

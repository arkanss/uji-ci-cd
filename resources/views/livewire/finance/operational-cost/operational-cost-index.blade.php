<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Operational Costs</flux:heading>
            <flux:subheading>Record and manage daily operational expenses</flux:subheading>
        </div>

        <div>
            <a href="{{ route('finance.operational-cost.create') }}">
                <flux:button variant="primary" icon="plus">Add Record</flux:button>
            </a>
        </div>
    </div>
    <div class="space-y-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search costs..."
            class="max-w-sm" />
        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Title</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Created By</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($costs as $cost)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            wire:key="{{ $cost->id }}">
                            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $cost->title }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                Rp {{ number_format($cost->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500">
                                {{ \Carbon\Carbon::parse($cost->date)->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($cost->creator)
                                    <flux:badge icon="user-circle" color="indigo" variant="subtle">
                                        {{ $cost->creator->name }}
                                    </flux:badge>
                                @else
                                    <flux:badge icon="cpu-chip" color="amber" variant="subtle">
                                        System
                                    </flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />
                                    <flux:menu>
                                        @if ($cost->attachments_url)
                                            <flux:menu.item icon="paper-clip"
                                               href="{{ $cost->attachments_url }}" target="_blank">View
                                                Attachment</flux:menu.item>
                                        @endif
                                        <flux:menu.item icon="pencil-square" href="{{ route('finance.operational-cost.edit', $cost->id) }}">
                                            Edit</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="delete('{{ $cost->id }}')"
                                            wire:confirm="Delete this record?">Delete</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-zinc-500">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $costs->links() }}</div>
    </div>

    {{-- Create/Edit handled in separate form page --}}
</div>

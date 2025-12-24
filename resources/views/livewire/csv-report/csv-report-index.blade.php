<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-6">
        <flux:heading size="xl" class="mb-1">CSV Reports</flux:heading>
        <flux:subheading>Monitor and view scheduled CSV import results</flux:subheading>
    </div>

    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">#</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">File Name</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">CSV Type</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Total Success</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Total Failed</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Status</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($jobs as $job)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors" wire:key="{{ $job->id }}">
                        <td class="px-4 py-3 text-sm font-mono text-zinc-500">
                            {{ Str::limit($job->id, 8, '...') }}
                        </td>

                        <td class="px-4 py-3 text-sm font-medium text-green-600 dark:text-green-500">
                            {{ $job->file_name }}
                        </td>

                        <td class="px-4 py-3">
                            <flux:badge size="sm" variant="outline" class="uppercase">{{ $job->type_name }}</flux:badge>
                        </td>

                        <td class="px-4 py-3 text-center text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $job->total_success }}
                        </td>

                        <td class="px-4 py-3 text-center text-sm font-medium text-red-600 dark:text-red-500">
                            {{ $job->total_failed }}
                        </td>

                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $job->created_at->format('d/M/Y, H:i') }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            @php
                                $color = match($job->status) {
                                    2 => 'success',
                                    3 => 'warning',
                                    4 => 'danger',
                                    default => 'success'
                                };
                            @endphp
                            <flux:badge color="{{ $color }}" variant="outline" size="sm" class="font-bold">
                                {{ $job->status_name }}
                            </flux:badge>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <flux:button size="sm" variant="ghost" icon="eye" wire:click="showDetail('{{ $job->id }}')">
                                View
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-zinc-500">No reports found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $jobs->links() }}
    </div>

    <flux:modal name="csv-detail-modal" class="md:w-[900px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Job Details</flux:heading>
                <flux:subheading>Isi data dari file: {{ $selectedJob?->file_name }}</flux:subheading>
            </div>

            <div class="max-h-[500px] overflow-y-auto border border-zinc-200 dark:border-zinc-800 rounded-lg">
                <table class="w-full text-left text-sm">
                    <thead class="sticky top-0 bg-zinc-100 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-2 border-b">Row Data (Name)</th>
                            <th class="px-4 py-2 border-b text-center">Status</th>
                            <th class="px-4 py-2 border-b">Remarks/Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($details as $detail)
                            <tr>
                                <td class="px-4 py-2">
                                    {{ $detail->data_name }}
                                    <div class="text-[10px] text-zinc-400 truncate w-48">{{ json_encode($detail->data) }}</div>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <flux:badge size="sm" color="{{ $detail->status == 2 ? 'success' : 'danger' }}">
                                        {{ $detail->status_name }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-2 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ $detail->remarks ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Close</flux:button>
                </flux:modal.close>
                @if($selectedJob?->file_url)
                    <flux:button as="a" href="{{ $selectedJob->file_url }}" target="_blank" icon="arrow-down-tray">
                        Download Original File
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>
</div>
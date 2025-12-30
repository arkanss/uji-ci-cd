<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-6">
        <flux:heading size="xl" class="mb-1">CSV Reports</flux:heading>
        <flux:subheading>Monitor and view scheduled CSV import results</flux:subheading>
    </div>

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
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
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                        wire:key="{{ $job->id }}">
                        <td class="px-4 py-3 text-sm font-mono text-zinc-500">
                            {{ Str::limit($job->id, 8, '...') }}
                        </td>

                        <td class="px-4 py-3 text-sm font-medium text-green-600 dark:text-green-500">
                            {{ $job->file_name }}
                        </td>

                        <td class="px-4 py-3">
                            <flux:badge size="sm" variant="outline" class="uppercase">{{ $job->type_name }}
                            </flux:badge>
                        </td>

                        <td class="px-4 py-3 text-center text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $job->total_success }}
                        </td>

                        <td class="px-4 py-3 text-center text-sm font-medium text-red-600 dark:text-red-500">
                            {{ $job->total_failed }}
                        </td>

                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $job->created_at->format('d M Y H:i') }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            @php
                                $color = match ($job->status) {
                                    2 => 'success',
                                    3 => 'warning',
                                    4 => 'danger',
                                    default => 'success',
                                };
                            @endphp
                            <flux:badge color="{{ $color }}" variant="outline" size="sm" class="font-bold">
                                {{ $job->status_name }}
                            </flux:badge>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <flux:button size="sm" variant="ghost" icon="eye"
                                wire:click="showDetail('{{ $job->id }}')">
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

    <flux:modal name="csv-detail-modal" class="md:w-[800px] !p-0 overflow-hidden">
        <div class="bg-zinc-50 dark:bg-zinc-950 p-6 space-y-6">
            <div>
                <flux:heading size="lg">Job Details</flux:heading>
                <flux:subheading>Informasi detail untuk file: {{ $selectedJob?->file_name }}</flux:subheading>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm">
                <flux:heading class="mb-6">CSV Job Information</flux:heading>

                <div class="space-y-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">CSV Job ID</div>
                            <div class="text-sm font-mono mt-1 break-all pr-4">{{ $selectedJob?->id }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-1">Status</div>
                            <flux:badge color="{{ $color }}" variant="solid" size="sm" class="font-bold">
                                {{ $selectedJob?->status_name }}
                            </flux:badge>
                        </div>
                    </div>

                    @if ($selectedJob?->failure_reason || $selectedJob?->status == 4)
                        <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                            <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Failure Reason</div>
                            <div class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium italic">
                                {{ $selectedJob?->failure_reason ?? 'failed to download file: failed to create reader: storage: object doesn\'t exist' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm">
                <flux:heading class="mb-6">Details</flux:heading>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">File Name</div>
                        <div class="text-sm font-medium text-green-600 dark:text-green-500 mt-1">
                            {{ $selectedJob?->file_name }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">CSV Type</div>
                        <div class="mt-1">
                            <flux:badge size="sm" variant="solid" class="uppercase">{{ $selectedJob?->type_name }}
                            </flux:badge>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">CSV Upload Date</div>
                        <div class="text-sm mt-1">{{ $selectedJob?->created_at?->format('d M Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Total Records</div>
                        <div class="text-lg font-semibold mt-1">
                            {{ $selectedJob?->total_records ?? $selectedJob?->total_success + $selectedJob?->total_failed }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Total Success</div>
                        <div class="text-lg font-semibold text-green-600 mt-1">{{ $selectedJob?->total_success }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Total Failed</div>
                        <div class="text-lg font-semibold text-red-600 mt-1">{{ $selectedJob?->total_failed }}</div>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <flux:heading size="sm">Row Level Results</flux:heading>
                <div class="max-h-[300px] overflow-y-auto border border-zinc-200 dark:border-zinc-800 rounded-lg">
                    <table class="w-full text-left text-sm">
                        <thead class="sticky top-0 bg-zinc-100 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-2 border-b">Row Data</th>
                                <th class="px-4 py-2 border-b text-center">Status</th>
                                <th class="px-4 py-2 border-b">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($details as $detail)
                                <tr class="bg-white dark:bg-zinc-900">
                                    <td class="px-4 py-2">
                                        <div class="font-medium">{{ $detail->data_name }}</div>
                                        <div class="text-[10px] text-zinc-400 truncate w-64">
                                            {{ json_encode($detail->data) }}</div>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <flux:badge size="sm"
                                            color="{{ $detail->status == 2 ? 'success' : 'danger' }}">
                                            {{ $detail->status_name }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-2 text-xs text-zinc-500 italic">
                                        {{ $detail->remarks ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" class="outline outline-2">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

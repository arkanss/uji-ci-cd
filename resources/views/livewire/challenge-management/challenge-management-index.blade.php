<div class="p-6 max-w-7xl mx-auto">
    {{-- Header Section --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Challenge Management</flux:heading>
            <flux:subheading>Atur dan pantau semua tantangan aktif untuk pengguna</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">Add Challenge</flux:button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 mb-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-4 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari judul challenge..."
            class="max-w-sm" />
    </div>

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[1000px]">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Judul</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Tipe</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Trigger</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Reward</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($challenges as $challenge)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                        wire:key="{{ $challenge->id }}">
                        <td class="px-4 py-3">
                            <span class="text-sm font-medium">{{ $challenge->name }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $challenge->type->label() }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <flux:badge size="sm" inset="top bottom" color="zinc">
                                {{ $challenge->trigger_type->label() }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $color = match ($challenge->status) {
                                    \App\Enums\ChallengeStatusEnum::Active => 'green',
                                    \App\Enums\ChallengeStatusEnum::Draft => 'zinc',
                                    \App\Enums\ChallengeStatusEnum::Ended => 'red',
                                    default => 'yellow',
                                };
                            @endphp
                            <flux:badge size="sm" :color="$color" variant="pill">
                                {{ $challenge->status->label() }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="font-medium text-zinc-600 dark:text-zinc-400">
                                {{ $challenge->rewards->count() }} Item
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil-square" wire:click="edit('{{ $challenge->id }}')">
                                        Edit
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="deleteChallenge('{{ $challenge->id }}')"
                                        wire:confirm="Apakah Anda yakin ingin menghapus challenge ini?">
                                        Hapus
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-zinc-500 italic">
                            Tidak ada data challenge ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <flux:modal name="create-challenge" class="md:min-w-[800px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Create New Challenge</flux:heading>
                <flux:subheading>Isi detail informasi tantangan baru di bawah ini.</flux:subheading>
            </div>

            <form wire:submit.prevent="save" class="space-y-8">

                <div class="space-y-4">
                    <flux:heading level="3" size="sm" class="uppercase tracking-wider text-zinc-500">1. Basic
                        Information</flux:heading>
                    <flux:separator variant="subtle" />
                    <div class="grid grid-cols-1 gap-4">
                        <flux:input wire:model="name" label="Challenge Name"
                            placeholder="Contoh: Puasa Gadget 24 Jam" />

                        <flux:textarea wire:model="description" label="Description"
                            placeholder="Jelaskan tentang tantangan ini..." rows="4" />

                        <flux:input wire:model="image" type="file" label="Banner Image" />
                    </div>
                </div>

                <div class="space-y-4">
                    <flux:heading level="3" size="sm" class="uppercase tracking-wider text-zinc-500">2.
                        Configuration</flux:heading>
                    <flux:separator variant="subtle" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:select wire:model="type" label="Challenge Type">
                            <option value="">Pilih Tipe</option>
                            @foreach ($challengeTypes as $typeOption)
                                <option value="{{ $typeOption->value }}">{{ $typeOption->label() }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model="trigger_type" label="Trigger Type">
                            <option value="">Pilih Trigger</option>
                            @foreach ($triggerTypes as $triggerOption)
                                <option value="{{ $triggerOption->value }}">{{ $triggerOption->label() }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input wire:model="target" type="number" label="Target Value" placeholder="e.g. 10000" />

                        <flux:select wire:model="status" label="Initial Status">
                            @foreach ($statuses as $statusOption)
                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                <div class="space-y-4">
                    <flux:heading level="3" size="sm" class="uppercase tracking-wider text-zinc-500">3.
                        Schedule</flux:heading>
                    <flux:separator variant="subtle" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model="start_date" type="datetime-local" label="Start Date" />
                        <flux:input wire:model="end_date" type="datetime-local" label="End Date" />
                    </div>
                </div>

                <div class="space-y-4">
                    <flux:heading level="3" size="sm" class="uppercase tracking-wider text-zinc-500">4.
                        Additional info</flux:heading>
                    <flux:separator variant="subtle" />
                    <div class="space-y-4">
                        <flux:textarea wire:model="term_and_condition" label="Terms & Conditions"
                            placeholder="Syarat dan ketentuan..." rows="3" />
                        <flux:textarea wire:model="how_to_join" label="How to Join"
                            placeholder="Cara mengikuti tantangan..." rows="3" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-1 py-4">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">
                        {{ $isEdit ? 'Update Challenge' : 'Simpan Challenge' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

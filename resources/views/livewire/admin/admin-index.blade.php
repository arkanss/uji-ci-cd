<div class="p-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Admin Management</flux:heading>
            <flux:subheading>Manage system administrators</flux:subheading>
        </div>

        {{-- Tombol ditambahkan wire:click="create" --}}
        <flux:button variant="primary" icon="plus" wire:click="create">
            Tambah Admin
        </flux:button>
    </div>

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Nama</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Email</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Nomor Telepon
                    </th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase text-center">
                        Role</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase text-center">
                        Status</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase text-right">
                        Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($admins as $admin)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                        wire:key="{{ $admin->id }}">
                        <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $admin->name }}
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $admin->email }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $admin->phone }}</td>
                        <td class="px-4 py-3 text-center">
                            <flux:badge>{{ $admin->role }}</flux:badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <flux:switch :checked="$admin->is_active"
                                wire:click="toggleActive('{{ $admin->id }}')" />
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                    inset="top bottom" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil-square" wire:click="edit('{{ $admin->id }}')">
                                        Edit Admin
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="delete('{{ $admin->id }}')"
                                        wire:confirm="Yakin mau hapus admin ini?">
                                        Hapus Admin
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <flux:icon.user-circle class="size-8 text-zinc-300" />
                                <span class="text-zinc-500 text-sm">Belum ada admin</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $admins->links() }}
    </div>

    {{-- MODAL TAMBAH/EDIT ADMIN --}}
    {{-- MODAL TAMBAH/EDIT ADMIN --}}
    <flux:modal name="admin-modal" class="md:w-[800px]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="xl">{{ $isEdit ? 'Edit Admin' : 'Create Admin' }}</flux:heading>
                <flux:subheading>Admins > {{ $isEdit ? 'Edit' : 'Create' }}</flux:subheading>
            </div>

            {{-- Grid System 2 Kolom sesuai gambar --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Kolom Kiri --}}
                <div class="space-y-4">
                    <flux:input label="Name" placeholder="Enter name" wire:model="name" required />

                    <flux:input label="Email verified at" type="datetime-local" wire:model="email_verified_at" />

                    <flux:input label="Phone" placeholder="Enter phone number" wire:model="phone" />
                </div>

                {{-- Kolom Kanan --}}
                <div class="space-y-4">
                    <flux:input label="Email address" type="email" placeholder="Enter email" wire:model="email"
                        required />

                    <flux:input label="Password" type="password" viewable wire:model="password"
                        :placeholder="$isEdit ? 'Leave blank to keep current' : 'Enter password'" />

                    {{-- Avatar Upload --}}
                    <flux:field>
                        <flux:label>Avatar</flux:label>
                        <div
                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-zinc-300 dark:border-zinc-700 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <flux:icon.photo class="mx-auto size-12 text-zinc-400" />
                                <div class="flex text-sm text-zinc-600 dark:text-zinc-400">
                                    <label
                                        class="relative cursor-pointer bg-transparent rounded-md font-medium text-indigo-600 hover:text-indigo-500">
                                        <span>Drag & Drop your files or <span class="underline">Browse</span></span>
                                        <input type="file" wire:model="avatar" class="sr-only">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </flux:field>
                </div>
            </div>

            {{-- Is Active Switch --}}
            <div class="flex items-center gap-3">
                <flux:switch wire:model="is_active" />
                <flux:label>Is active <span class="text-red-500">*</span></flux:label>
            </div>

            {{-- Footer Buttons --}}
            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">
                    {{ $isEdit ? 'Update' : 'Create' }}
                </flux:button>

                @if (!$isEdit)
                    <flux:button wire:click="saveAndCreateAnother" variant="filled">Create & create another
                    </flux:button>
                @endif

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>
</div>

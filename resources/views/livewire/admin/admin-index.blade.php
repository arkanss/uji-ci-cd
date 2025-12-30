<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Admin Management</flux:heading>
            <flux:subheading>Manage system administrators</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create">
            Add Admin
        </flux:button>
    </div>

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Name</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Email</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Phone Number
                    </th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase text-center">
                        Role</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase text-right">
                        Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($admins as $admin)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                        wire:key="{{ $admin->id }}">
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $admin->name }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $admin->email }}
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $admin->phone_number ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $roleLabel = match ($admin->role) {
                                    2 => 'Admin',
                                    7 => 'Accounting',
                                    9 => 'Warehouse',
                                    default => 'User',
                                };
                                $roleColor = match ($admin->role) {
                                    2 => 'indigo',
                                    7 => 'emerald',
                                    9 => 'orange',
                                    default => 'zinc',
                                };
                            @endphp
                            <flux:badge color="{{ $roleColor }}" variant="outline">{{ $roleLabel }}</flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />

                                    <flux:menu>
                                        <flux:menu.item icon="eye" wire:click="showDetail('{{ $admin->id }}')">
                                            Detail</flux:menu.item>
                                        <flux:menu.item icon="pencil-square" wire:click="edit('{{ $admin->id }}')">
                                            Edit</flux:menu.item>

                                        <flux:menu.separator />

                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="confirmDelete('{{ $admin->id }}')">
                                            Delete
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-zinc-500">Not Found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $admins->links() }}
    </div>

    <flux:modal name="admin-modal" class="md:w-[550px]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="xl">{{ $isEdit ? 'Edit Admin' : 'Create Admin' }}</flux:heading>
                <flux:subheading>Enter the detailed information for the system administrator.</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:input label="Name" placeholder="Enter name" wire:model="name" required class="w-full" />

                <flux:input label="Email address" type="email" placeholder="Enter email" wire:model="email" required
                    class="w-full" />

                <flux:input label="Phone Number" placeholder="e.g. 0812..." wire:model="phone_number" class="w-full" />

                <flux:select label="Role" wire:model="role" required class="w-full">
                    <option value="2">Admin</option>
                    <option value="7">Accounting</option>
                    <option value="9">Admin Warehouse</option>
                </flux:select>

                <flux:input label="Password" type="password" viewable wire:model="password"
                    :placeholder="$isEdit ? 'Kosongkan jika tidak ingin ganti' : 'Enter password'" class="w-full" />
            </div>

            <div class="flex gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                @if (!$isEdit)
                    <flux:button wire:click="saveAndCreateAnother" variant="filled" wire:loading.attr="disabled">
                        Create & Create Another
                    </flux:button>
                @endif

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Update Admin' : 'Save Admin' }}</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="detail-modal" class="md:w-[500px]">
        @if ($selectedUser)
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div
                        class="size-20 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center overflow-hidden border border-zinc-200 dark:border-zinc-700">
                        @if ($selectedUser->avatar)
                            <img src="{{ $selectedUser->avatar }}" class="object-cover size-full">
                        @else
                            <flux:icon.user class="size-10 text-zinc-400" />
                        @endif
                    </div>
                    <div>
                        <flux:heading size="lg">{{ $selectedUser->name }}</flux:heading>
                        <code
                            class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded text-zinc-600 dark:text-zinc-400">
                            {{ $selectedUser->user_code }}
                        </code>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-4 text-sm bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl">
                    <div class="text-zinc-500">Email Address</div>
                    <div class="text-zinc-900 dark:text-zinc-100 break-all">{{ $selectedUser->email }}</div>

                    <div class="text-zinc-500">Phone Number</div>
                    <div class="text-zinc-900 dark:text-zinc-100">{{ $selectedUser->phone_number ?? '-' }}</div>

                    <div class="text-zinc-500">System Role</div>
                    <div>
                        <flux:badge size="sm" inset="top bottom">
                            {{ match ($selectedUser->role) {2 => 'Admin',7 => 'Accounting',9 => 'Warehouse',default => '-'} }}
                        </flux:badge>
                    </div>

                    <div class="text-zinc-500">Joined Since</div>
                    <div class="text-zinc-900 dark:text-zinc-100">
                        {{ $selectedUser->created_at?->format('d M Y, H:i') }}</div>
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

    <flux:modal name="delete-admin-modal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Admin?</flux:heading>
                <flux:text class="mt-2">
                    You're about to delete this administrator account.<br>
                    This action cannot be reversed.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">
                    Delete Admin
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

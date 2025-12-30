<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Product Categories</flux:heading>
            <flux:subheading>Manage your product categories</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create">
            Add Category
        </flux:button>
    </div>

    <div class="space-y-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search categories..."
            class="max-w-sm" />

        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Category
                        </th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Description
                        </th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Created at
                        </th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Updated at
                        </th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase text-right">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            wire:key="{{ $category->id }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex-shrink-0 border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                        @if ($category->image)
                                            <img src="{{ $category->image }}" class="object-cover w-full h-full">
                                        @else
                                            <div class="h-full w-full flex items-center justify-center">
                                                <flux:icon.photo class="size-4 text-zinc-400" />
                                            </div>
                                        @endif
                                    </div>
                                    <span
                                        class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400 max-w-xs">
                                <span class="line-clamp-2">{{ $category->description ?: '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                {{ $category->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                {{ $category->updated_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil-square" wire:click="edit('{{ $category->id }}')">
                                            Edit
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="confirmDelete('{{ $category->id }}')">
                                            Delete
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <flux:icon.magnifying-glass class="size-8 text-zinc-300" />
                                    <span class="text-zinc-500 text-sm">No categories found</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $categories->links() }}</div>
    </div>

    <flux:modal name="category-modal" class="md:w-[500px]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingCategoryId ? 'Update Category' : 'Create Category' }}
                </flux:heading>
                <flux:subheading>Fill in the category details</flux:subheading>
            </div>

            <div class="space-y-5">
                <flux:field>
                    <flux:label>Name</flux:label>
                    <flux:input wire:model="name" placeholder="Category name" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="description" placeholder="Describe this category..." rows="3" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Category Image</flux:label>
                    <div class="relative group w-full">
                        <div
                            class="w-full h-36 rounded-xl border-2 border-dashed border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center overflow-hidden transition-all group-hover:border-primary-500">
                            @if ($image)
                                <img src="{{ $image->temporaryUrl() }}" class="object-cover w-full h-full">
                            @elseif ($oldImage)
                                <img src="{{ $oldImage }}"
                                    class="object-cover w-full h-full">
                            @else
                                <div class="flex flex-col items-center gap-2 text-zinc-400">
                                    <flux:icon.photo class="size-8" />
                                    <span class="text-sm">Click to upload image</span>
                                </div>
                            @endif
                        </div>
                        <input type="file" wire:model="image" accept="image/*"
                            class="absolute inset-0 opacity-0 cursor-pointer">
                    </div>
                    <div wire:loading wire:target="image" class="text-xs text-primary-600 mt-1">Uploading...</div>
                    <flux:error name="image" />
                </flux:field>
            </div>

            <div class="flex gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    {{ $editingCategoryId ? 'Save Changes' : 'Create Category' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-category-modal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Category?</flux:heading>
                <flux:text class="mt-2">
                    You're about to delete this category permanently.<br>
                    This action cannot be undone.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">
                    Delete Category
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

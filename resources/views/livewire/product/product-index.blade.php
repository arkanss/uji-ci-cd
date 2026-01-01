<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">Products</flux:heading>
            <flux:subheading>Manage your inventory and specifications</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">Add Product</flux:button>
    </div>

    <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search name, SKU, brand, or code..." class="max-w-sm" />
        </div>

        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Product</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Price</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Category</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Code</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">SKU</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-center">Stock</th>
                        <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($products as $product)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            wire:key="{{ $product->id }}">
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium">{{ $product->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <flux:badge size="sm" inset="top bottom" color="zinc">
                                    {{ $product->category?->name ?? 'Uncategorized' }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-zinc-600">
                                {{ $product->code ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-zinc-500">
                                {{ $product->sku ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                <span class="{{ $product->stock <= 5 ? 'text-red-600 font-bold' : '' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-zinc-500">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye" wire:click="showDetail('{{ $product->id }}')">
                                            Details</flux:menu.item>
                                        <flux:menu.item icon="pencil-square" wire:click="edit('{{ $product->id }}')">
                                            Edit</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="delete('{{ $product->id }}')" wire:confirm="Are you sure?">
                                            Delete</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-10 text-center text-zinc-500 italic">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>

    <flux:modal name="product-modal" class="w-full max-w-6xl space-y-0 p-0">
        @isset($form)
            <form wire:submit="save" class="flex flex-col h-full max-h-[85vh]">
                <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-700 flex-shrink-0">
                    <flux:heading size="lg">{{ $isEdit ? 'Update Product' : 'New Product' }}</flux:heading>
                    <flux:subheading class="mt-1">Complete product information and specifications</flux:subheading>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div class="px-6 py-6 space-y-8">
                        <div class="space-y-5">
                            <h3
                                class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                Basic Information
                            </h3>

                            <div class="space-y-2">
                                <flux:label>Product Image</flux:label>
                                <div class="flex items-start gap-4">
                                    <label for="product-image-upload" class="cursor-pointer group">
                                        <div
                                            class="size-24 rounded-lg border-2 border-dashed border-zinc-300 dark:border-zinc-600 overflow-hidden bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center flex-shrink-0 hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors">
                                            @if (isset($form->image) && method_exists($form->image, 'temporaryUrl'))
                                                <img src="{{ $form->image->temporaryUrl() }}"
                                                    class="object-cover size-full">
                                            @elseif (!empty($form->oldImage))
                                                <img src="{{ $form->oldImage }}"
                                                    class="object-cover size-full">
                                            @else
                                                <div class="text-center">
                                                    <flux:icon.photo
                                                        class="size-8 text-zinc-400 dark:text-zinc-500 mx-auto mb-1 group-hover:text-zinc-500 dark:group-hover:text-zinc-400 transition-colors" />
                                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Click to
                                                        upload</span>
                                                </div>
                                            @endif
                                        </div>
                                    </label>
                                    <div class="flex-1 space-y-1">
                                        <input type="file" id="product-image-upload" wire:model="form.image"
                                            class="hidden" accept="image/*" />
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                            <p class="font-medium">Upload product photo</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-500 mt-0.5">JPG, PNG or WEBP
                                                (max. 5MB)</p>
                                        </div>
                                        <flux:error name="form.image" />
                                        @if (isset($form->image) || !empty($form->oldImage))
                                            <button type="button" wire:click="$set('form.image', null)"
                                                class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 mt-2">
                                                Remove image
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <flux:label>Product Name</flux:label>
                                <flux:input wire:model="form.name" placeholder="Enter product name" />
                                <flux:error name="form.name" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <flux:label>Category</flux:label>
                                    <flux:select wire:model="form.category_id">
                                        <option value="">Select category</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="form.category_id" />
                                </div>
                                <div class="space-y-2">
                                    <flux:label>Brand</flux:label>
                                    <flux:input wire:model="form.brand" placeholder="Enter brand name" />
                                    <flux:error name="form.brand" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <flux:label>Description</flux:label>
                                <flux:textarea wire:model="form.description" rows="3"
                                    placeholder="Product description" />
                                <flux:error name="form.description" />
                            </div>
                        </div>

                        <!-- Pricing & Inventory -->
                        <div class="space-y-5">
                            <h3
                                class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                Pricing & Inventory
                            </h3>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <flux:label>Price</flux:label>

                                    <div x-data="{
                                        rawPrice: @entangle('form.price'),
                                    
                                        get formatted() {
                                            if (!this.rawPrice) return '';
                                            return this.rawPrice
                                                .toString()
                                                .replace(/\D/g, '')
                                                .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                        },
                                    
                                        updateValue(e) {
                                            let val = e.target.value.replace(/\D/g, '');
                                            this.rawPrice = val;
                                        }
                                    }">
                                        <flux:input.group>
                                            <flux:input.group.prefix class="font-semibold">
                                                Rp
                                            </flux:input.group.prefix>

                                            <flux:input type="text" placeholder="0" x-bind:value="formatted"
                                                x-on:input="updateValue($event)" />
                                        </flux:input.group>
                                    </div>

                                    <flux:error name="form.price" />
                                </div>

                                <div class="space-y-2">
                                    <flux:label>Discount</flux:label>
                                    <flux:input type="number" wire:model="form.discount" placeholder="0" />
                                    <flux:error name="form.discount" />
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <flux:label>SKU</flux:label>
                                    <flux:input wire:model="form.sku" placeholder="Product SKU" />
                                    <flux:error name="form.sku" />
                                </div>
                                <div class="space-y-2">
                                    <flux:label>Code</flux:label>
                                    <flux:input wire:model="form.code" placeholder="Barcode" />
                                    <flux:error name="form.code" />
                                </div>
                                <div class="space-y-2">
                                    <flux:label>Stock</flux:label>
                                    <flux:input type="number" wire:model="form.stock" placeholder="0" />
                                    <flux:error name="form.stock" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <flux:label>Featured Product</flux:label>
                                <flux:switch wire:model="form.featured" label="Display as featured product" />
                            </div>
                        </div>

                        <div class="space-y-5">
                            <h3
                                class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                Loyalty & Rewards
                            </h3>

                            <div class="grid grid-cols-4 gap-4">
                                <div class="space-y-2">
                                    <flux:label>Point Value</flux:label>
                                    <flux:input type="number" wire:model="form.point_value" placeholder="0" />
                                    <flux:error name="form.point_value" />
                                </div>
                                <div class="space-y-2">
                                    <flux:label>Reward Type</flux:label>
                                    <flux:input type="number" wire:model="form.reward_type"
                                        placeholder="Reward type ID" />
                                    <flux:error name="form.reward_type" />
                                </div>
                                <div class="space-y-2">
                                    <flux:label>Level Zero Reward</flux:label>
                                    <flux:input type="number" wire:model="form.level_zero_reward" placeholder="0" />
                                    <flux:error name="form.level_zero_reward" />
                                </div>
                                <div class="space-y-2">
                                    <flux:label>Level One Reward</flux:label>
                                    <flux:input type="number" wire:model="form.level_one_reward" placeholder="0" />
                                    <flux:error name="form.level_one_reward" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <flux:label>Level Medal ID</flux:label>
                                <flux:input type="number" wire:model="form.level_medal_id" placeholder="Medal ID" />
                                <flux:error name="form.level_medal_id" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                <flux:label class="pt-1">
                                    Scope Service <span class="text-red-500">*</span>
                                </flux:label>

                                <div class="md:col-span-3">
                                    <div x-data="{
                                        selected: @entangle('form.scope_service'),
                                        options: [
                                            { value: 'local_place', label: 'Local Place' },
                                            { value: 'local_health', label: 'Local Health' },
                                            { value: 'khas', label: 'KHAS Indonesia' },
                                        ],
                                        toggle(value) {
                                            this.selected.includes(value) ?
                                                this.selected = this.selected.filter(v => v !== value) :
                                                this.selected.push(value)
                                        }
                                    }" class="flex flex-wrap gap-2">

                                        <template x-for="option in options" :key="option.value">
                                            <button type="button" @click="toggle(option.value)"
                                                :class="selected.includes(option.value) ?
                                                    'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-200' :
                                                    'bg-white text-zinc-700 border-zinc-300 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-300 dark:border-zinc-700 dark:hover:bg-zinc-800'"
                                                class="px-4 py-1.5 text-xs font-medium rounded-full border transition focus:outline-none">
                                                <span x-text="option.label"></span>
                                            </button>
                                        </template>
                                    </div>

                                    <flux:error name="form.scope_service" />
                                </div>
                            </div>


                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                <flux:label class="pt-1">
                                    Scope Type
                                </flux:label>

                                <div class="md:col-span-3">
                                    <div x-data="{
                                        selected: @entangle('form.scope_type'),
                                        options: [
                                            { value: 'local_place', label: 'Local Place' },
                                            { value: 'local_health', label: 'Local Health' },
                                            { value: 'khas', label: 'KHAS Indonesia' },
                                        ],
                                        toggle(value) {
                                            this.selected.includes(value) ?
                                                this.selected = this.selected.filter(v => v !== value) :
                                                this.selected.push(value)
                                        }
                                    }" class="flex flex-wrap gap-2">

                                        <template x-for="option in options" :key="option.value">
                                            <button type="button" @click="toggle(option.value)"
                                                :class="selected.includes(option.value) ?
                                                    'bg-emerald-600 text-white border-emerald-600 ring-2 ring-emerald-200' :
                                                    'bg-white text-zinc-700 border-zinc-300 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-300 dark:border-zinc-700 dark:hover:bg-zinc-800'"
                                                class="px-4 py-1.5 text-xs font-medium rounded-full border transition focus:outline-none">
                                                <span x-text="option.label"></span>
                                            </button>
                                        </template>
                                    </div>

                                    <flux:error name="form.scope_type" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <h3
                                class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                Shipping & Dimensions
                            </h3>

                            <div class="grid grid-cols-4 gap-3">
                                <div class="space-y-2">
                                    <flux:label>Weight (gr)</flux:label>
                                    <flux:input type="number" wire:model="form.weight" placeholder="0" />
                                    <flux:error name="form.weight" />
                                </div>
                                <div class="space-y-2">
                                    <flux:label>Length (cm)</flux:label>
                                    <flux:input type="number" wire:model="form.length" placeholder="0" />
                                    <flux:error name="form.length" />
                                </div>
                                <div class="space-y-2">
                                    <flux:label>Width (cm)</flux:label>
                                    <flux:input type="number" wire:model="form.width" placeholder="0" />
                                    <flux:error name="form.width" />
                                </div>
                                <div class="space-y-2">
                                    <flux:label>Height (cm)</flux:label>
                                    <flux:input type="number" wire:model="form.height" placeholder="0" />
                                    <flux:error name="form.height" />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div
                    class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 flex items-center justify-end gap-3 flex-shrink-0">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" icon="check">
                        Save Product
                    </flux:button>
                </div>
            </form>
        @endisset
    </flux:modal>

    <flux:modal name="detail-modal" class="md:w-[500px]">
        @if ($selectedProduct)
            <div class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="size-24 rounded-lg border overflow-hidden bg-zinc-100 dark:bg-zinc-800 flex-shrink-0">
                        @if ($selectedProduct->image)
                            <img src="{{ $selectedProduct->image }}" class="object-cover size-full">
                        @else
                            <flux:icon.photo class="p-6 text-zinc-300 size-full" />
                        @endif
                    </div>
                    <div class="min-w-0">
                        <flux:heading size="xl" class="truncate">{{ $selectedProduct->name }}</flux:heading>
                        <flux:subheading>{{ $selectedProduct->category?->name ?? 'Uncategorized' }}</flux:subheading>
                        <div class="mt-1">
                            <flux:badge size="sm" color="blue" inset="top bottom">
                                {{ $selectedProduct->brand ?: 'No Brand' }}</flux:badge>
                        </div>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 gap-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-100 dark:border-zinc-800">
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase tracking-wider text-zinc-500 font-bold">Price</span>
                        <span class="text-sm font-mono text-blue-600 dark:text-blue-400">
                            Rp {{ number_format($selectedProduct->price, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase tracking-wider text-zinc-500 font-bold">Stock</span>
                        <span class="text-sm">{{ $selectedProduct->stock }} units</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase tracking-wider text-zinc-500 font-bold">SKU</span>
                        <span class="text-sm font-mono">{{ $selectedProduct->sku ?: '-' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase tracking-wider text-zinc-500 font-bold">Code</span>
                        <span class="text-sm font-mono">{{ $selectedProduct->code ?: '-' }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="text-sm font-bold text-zinc-800 dark:text-zinc-200">Description</span>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        {{ $selectedProduct->description ?: 'No description provided.' }}
                    </p>
                </div>

                <div class="flex pt-4">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Close</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @else
            <div class="p-10 flex justify-center items-center flex-col gap-2">
                <div class="animate-spin size-6 border-2 border-blue-600 border-t-transparent rounded-full"></div>
                <span class="text-zinc-400 text-sm">Loading details...</span>
            </div>
        @endif
    </flux:modal>
</div>

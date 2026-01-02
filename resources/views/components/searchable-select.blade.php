{{-- resources/views/components/custom-flux-select.blade.php --}}
{{-- Free Flux Pro-style Searchable Select – Fully working with Livewire (no extra properties needed) --}}

@props([
'name' => null, {{-- Required: the Livewire property name, e.g. "currency" --}}
'options' => [], {{-- Array of ['value' => ..., 'label' => ..., 'description' => ... (optional), 'icon' => ... (optional)] --}}
'placeholder' => 'Select an option...',
'value' => null, {{-- Current selected value (passed from parent via :value="$property") --}}
'disabled' => false
])

<div
    x-data="fluxSelect({
        search: '',
        open: false,
        selected: @js($value),
        options: @js($options),
        model: @js($attributes->wire('model')->value)
    })"
    x-init="
        init();
        $watch('selected', value => {
            if (value !== @js($value)) {
                $wire.set(@js($attributes->wire('model')->value), value, true);
            }
        });
        $watch('selected', value => {
            if (value !== @js($value)) {
                $wire.set(model, value, true);
            }
        });

        $watch(() => $wire.get(model), value => {
            selected = value;
        });

    "
    @click.away="open = false"
    @keydown.escape.window="open = false"
    class="relative w-full"
    wire:ignore>
    <!-- Trigger Button – Matches your exact form field styling -->
    <button
        type="button"
        @click="open = !open"
        :disabled="{{ $disabled ? 'true' : 'false' }}"
        class="w-full border rounded-lg block disabled:shadow-none dark:shadow-none appearance-none text-base sm:text-sm py-2 h-10 leading-[1.375rem] ps-3 pe-3 bg-white dark:bg-white/10 dark:disabled:bg-white/[7%] text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5 data-[invalid]:shadow-none data-[invalid]:border-red-500 dark:data-[invalid]:border-red-500 disabled:data-[invalid]:border-red-500 dark:disabled:data-[invalid]:border-red-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:cursor-not-allowed transition-all duration-100"
        :class="{ 'ring-2 ring-blue-500 border-blue-500 shadow-md': open }"
        aria-haspopup="listbox"
        :aria-expanded="open">
        <span class="flex items-center justify-between w-full">
            <span class="truncate" x-show="selectedLabel">
                <span x-text="selectedLabel"></span>
            </span>
            <span class="text-zinc-400 dark:text-zinc-400" x-show="!selectedLabel">
                {{ $placeholder }}
            </span>
            <svg class="h-4 w-4 text-zinc-400 flex-shrink-0 transition-transform duration-100"
                :class="{ 'rotate-180': open }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>

    <!-- Dropdown Listbox -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-98"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-98"
        class="absolute z-50 mt-1 w-full rounded-xl shadow-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-zinc-900 backdrop-blur-sm overflow-hidden"
        role="listbox">
        <!-- Search Input -->
        <div class="sticky top-0 p-3 border-b border-zinc-200 dark:border-white/10 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm">
            <input
                type="text"
                x-model="search"
                @input="filterOptions"
                placeholder="Search..."
                class="w-full px-3 py-2 text-sm bg-white dark:bg-white/10 border border-zinc-200 dark:border-white/10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-100"
                x-ref="searchInput" />
        </div>

        <!-- Options List -->
        <div class="max-h-72 overflow-y-auto p-1">
            <!-- Empty State -->
            <template x-if="filtered.length === 0">
                <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <p>No options found</p>
                </div>
            </template>

            <!-- Options -->
            <template x-for="option in filtered" :key="option.value">
                <div
                    @click="select(option); open = false"
                    @mouseenter="highlightedIndex = filtered.indexOf(option)"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all duration-100 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                    :class="{
                        'bg-blue-50 dark:bg-blue-900/30 ring-2 ring-blue-500 ring-inset font-medium':
                            selected === option.value || highlightedIndex === filtered.indexOf(option)
                    }"
                    role="option"
                    :aria-selected="selected === option.value">
                    <!-- Optional Icon -->
                    <div x-show="option.icon" class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500/10 to-indigo-500/10 flex items-center justify-center flex-shrink-0">
                        <i :class="option.icon" class="text-blue-600 dark:text-blue-400 text-lg"></i>
                    </div>

                    <!-- Label & Description -->
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-blue-700 dark:group-hover:text-blue-400 truncate transition-colors duration-100"
                            x-text="option.label"></p>
                        <p x-show="option.description"
                            class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-100"
                            x-text="option.description"></p>
                    </div>

                    <!-- Selected Checkmark -->
                    <svg x-show="selected === option.value"
                        class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 transition-opacity duration-100"
                        fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    function fluxSelect({
        search,
        open,
        selected,
        options,
        name
    }) {
        return {
            search: search || '',
            open: open || false,
            selected: selected,
            options: options,
            name: name,
            filtered: options,
            highlightedIndex: -1,

            get selectedLabel() {
                const opt = this.options.find(o => o.value === this.selected);
                return opt ? opt.label : '';
            },

            init() {
                this.filterOptions();
                this.$watch('open', (value) => {
                    if (value) {
                        this.$nextTick(() => this.$refs?.searchInput?.focus());
                    } else {
                        this.search = '';
                        this.filterOptions();
                    }
                });
            },

            filterOptions() {
                if (!this.search.trim()) {
                    this.filtered = this.options;
                    return;
                }
                const query = this.search.toLowerCase();
                this.filtered = this.options.filter(option =>
                    option.label.toLowerCase().includes(query) ||
                    (option.description && option.description.toLowerCase().includes(query))
                );
                this.highlightedIndex = -1;
            },

            select(option) {
                this.selected = option.value;
                this.open = false;
            }
        }
    }
</script>
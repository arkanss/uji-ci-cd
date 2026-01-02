@props(['title' => null])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm']) }}>
    @if($title)
    <flux:heading size="sm" class="mb-4 bg-gray-100 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 text-sm font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ $title }}</flux:heading>
    @endif

    <div class="px-4 pb-4">
        {{ $slot }}
    </div>
</div>
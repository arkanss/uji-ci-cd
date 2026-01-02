# Custom Card Component Usage

## Overview

The `<x-ui.card>` component is a reusable wrapper that provides consistent styling across your application. It wraps the design system's card styling in a customizable component.

## Component Location

`resources/views/components/ui/card.blade.php`

## Basic Usage

### Simple Card
```blade
<x-ui.card>
    <p>Your content here</p>
</x-ui.card>
```

### Card with Title
```blade
<x-ui.card title="General Information">
    <div class="grid md:grid-cols-3 gap-4">
        <!-- Your content -->
    </div>
</x-ui.card>
```

### Card with Custom Classes
```blade
<x-ui.card class="mb-6 hover:shadow-lg">
    <p>Content with extra margin and hover effect</p>
</x-ui.card>
```

### Card without Title but with Custom Header
```blade
<x-ui.card>
    <div class="flex items-center justify-between mb-4">
        <flux:heading size="sm">Items</flux:heading>
        <flux:button variant="primary" icon="plus">Add Item</flux:button>
    </div>

    <!-- Your content -->
</x-ui.card>
```

## Customization

### Current Default Styling

```blade
bg-white dark:bg-zinc-900
border border-zinc-200 dark:border-zinc-800
rounded-lg
p-6
shadow-sm
```

### How to Customize

You can modify the component at `resources/views/components/ui/card.blade.php`:

#### Example 1: Add More Shadow
```blade
@props(['title' => null])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-6 shadow-md']) }}>
    @if($title)
    <flux:heading size="sm" class="mb-4">{{ $title }}</flux:heading>
    @endif

    {{ $slot }}
</div>
```

#### Example 2: Add Hover Effect
```blade
@props(['title' => null])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm transition-shadow hover:shadow-md']) }}>
    @if($title)
    <flux:heading size="sm" class="mb-4">{{ $title }}</flux:heading>
    @endif

    {{ $slot }}
</div>
```

#### Example 3: Different Padding
```blade
@props(['title' => null, 'compact' => false])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm ' . ($compact ? 'p-4' : 'p-6')]) }}>
    @if($title)
    <flux:heading size="sm" class="mb-4">{{ $title }}</flux:heading>
    @endif

    {{ $slot }}
</div>
```

Usage with compact:
```blade
<x-ui.card title="Compact Card" compact>
    Less padding content
</x-ui.card>
```

#### Example 4: Add Optional Variants
```blade
@props(['title' => null, 'variant' => 'default'])

@php
$variants = [
    'default' => 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800',
    'primary' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
    'success' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
    'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
];

$variantClass = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => $variantClass . ' border rounded-lg p-6 shadow-sm']) }}>
    @if($title)
    <flux:heading size="sm" class="mb-4">{{ $title }}</flux:heading>
    @endif

    {{ $slot }}
</div>
```

Usage with variants:
```blade
<x-ui.card title="Success Card" variant="success">
    Success content
</x-ui.card>
```

## Benefits

1. **Consistency** - All cards look the same across the application
2. **Maintainability** - Change styling in one place, affects all cards
3. **Flexibility** - Can still override classes per instance
4. **Reusability** - Easy to use throughout the codebase
5. **DRY Principle** - Don't repeat yourself

## Where It's Used

Currently used in:
- `resources/views/livewire/finance/purchase-order/purchase-order-form.blade.php`

You can replace all instances of `<flux:card>` with `<x-ui.card>` throughout your application for consistent styling.

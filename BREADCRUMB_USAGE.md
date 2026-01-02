# Breadcrumb Helper Usage Guide

## Helper Functions

Two helper functions are now available globally:

### 1. `breadcrumbs(...$items)`
Returns a formatted breadcrumbs array.

### 2. `set_breadcrumbs(...$items)`
Sets breadcrumbs in the view data (useful in controllers).

---

## Usage Examples

### In Livewire Components

```php
<?php

namespace App\Livewire\PurchaseOrder;

use Livewire\Component;

class PurchaseOrderIndex extends Component
{
    public function render()
    {
        return view('livewire.purchase-order.purchase-order-index', [
            'breadcrumbs' => breadcrumbs(
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Orders', 'url' => route('purchase-orders.index')],
                'List' // Current page (no URL)
            )
        ]);
    }
}
```

### In Controllers

```php
<?php

namespace App\Http\Controllers;

class OrderController extends Controller
{
    public function index()
    {
        set_breadcrumbs(
            ['label' => 'Home', 'url' => '/'],
            'Orders'
        );

        return view('orders.index');
    }

    public function create()
    {
        set_breadcrumbs(
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Orders', 'url' => route('orders.index')],
            'Create Order'
        );

        return view('orders.create');
    }
}
```

### Directly in Blade Views

```blade
@php
    $breadcrumbs = breadcrumbs(
        ['label' => 'Finance', 'url' => route('finance.index')],
        ['label' => 'Invoices', 'url' => route('finance.invoices.index')],
        'Invoice #12345'
    );
@endphp

<x-layout :breadcrumbs="$breadcrumbs">
    <!-- Your content -->
</x-layout>
```

### Simple String Breadcrumbs

```php
// All strings (no links)
$breadcrumbs = breadcrumbs('Home', 'Settings', 'Profile');
```

### Mixed Format

```php
// Mix of strings and arrays
$breadcrumbs = breadcrumbs(
    'Home', // String only
    ['label' => 'Products', 'url' => route('products.index')], // With URL
    'Edit Product' // Current page
);
```

---

## How It Works

1. The breadcrumb helper formats the data
2. The header component (`resources/views/components/header/main.blade.php`) automatically displays breadcrumbs when the `$breadcrumbs` variable is present
3. Breadcrumbs are styled with dark mode support using Flux UI icons
4. The last item is always displayed as bold text (current page)
5. All other items with URLs are clickable links

---

## Tips

- The last breadcrumb item is automatically styled as the current page (bold, non-clickable)
- Only add URLs to items that should be links
- Use `route()` helper for generating URLs to keep them consistent
- Keep breadcrumb labels short and descriptive

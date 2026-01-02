<?php

if (!function_exists('breadcrumbs')) {
    /**
     * Generate breadcrumbs array for the header component
     *
     * @param array $items Array of breadcrumb items. Each item can be:
     *                     - string: Will be treated as label only (no link)
     *                     - array: ['label' => 'Text', 'url' => 'https://...'] (optional url)
     * @return array
     *
     * @example
     * breadcrumbs('Home', ['label' => 'Orders', 'url' => route('orders.index')], 'Create')
     * breadcrumbs(['label' => 'Dashboard', 'url' => '/'], 'Settings', 'Profile')
     */
    function breadcrumbs(...$items): array
    {
        $breadcrumbs = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $breadcrumbs[] = ['label' => $item];
            } elseif (is_array($item)) {
                $breadcrumbs[] = $item;
            }
        }

        return $breadcrumbs;
    }
}

if (!function_exists('set_breadcrumbs')) {
    /**
     * Set breadcrumbs in the view data
     * This function can be used in controllers or Livewire components
     *
     * @param array $items
     * @return void
     *
     * @example
     * set_breadcrumbs('Home', ['label' => 'Orders', 'url' => route('orders.index')], 'Create')
     */
    function set_breadcrumbs(...$items): void
    {
        view()->share('breadcrumbs', breadcrumbs(...$items));
    }
}

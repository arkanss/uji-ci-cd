<flux:sidebar sticky collapsible="mobile"
    class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700
           w-[260px] lg:w-[280px] min-w-[260px] lg:min-w-[280px]">

    <flux:sidebar.header>
        <flux:sidebar.brand href="#" logo="{{ asset('images/logo-black.png') }}"
            logo:dark="{{ asset('images/logo_white.svg') }}" {{-- name="Local Place" --}} />
        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    <flux:sidebar.nav class="space-y-4">
        <flux:sidebar.item icon="home" href="{{ route('dashboard') }}"
            :current="request()->routeIs('dashboard')">
            Dashboard
        </flux:sidebar.item>

        <flux:sidebar.item icon="users" href="{{ route('admin.index') }}"
            :current="request()->routeIs('admin.index')">
            Admin Management
        </flux:sidebar.item>

        <flux:sidebar.item icon="document-chart-bar" href="{{ route('csv-reports.index') }}"
            :current="request()->routeIs('csv-reports.index')">
            CSV Reports
        </flux:sidebar.item>

        <flux:sidebar.group expandable heading="Operational" class="grid">
            <flux:sidebar.item icon="clipboard-document-check">DP Item Requests</flux:sidebar.item>

            <flux:sidebar.item icon="truck" href="{{ route('delivery.index') }}"
                :current="request()->routeIs('delivery.index')">
                Delivery
            </flux:sidebar.item>

            <flux:sidebar.item icon="banknotes" href="{{ route('operational-costs.index') }}"
                :current="request()->routeIs('operational-costs.index')">
                Operational Cost
            </flux:sidebar.item>

            <flux:sidebar.item icon="star">Point Management</flux:sidebar.item>
        </flux:sidebar.group>

        <flux:sidebar.item icon="document-text" href="{{ route('purchase-orders.index') }}"
            :current="request()->routeIs('purchase-orders.index')">
            Purchase Order
        </flux:sidebar.item>

        <flux:sidebar.item icon="shopping-cart">Create PO</flux:sidebar.item>
        <flux:sidebar.item icon="building-storefront">Merchants</flux:sidebar.item>
        <flux:sidebar.item icon="trophy">Challenge Management</flux:sidebar.item>

        <flux:sidebar.item icon="archive-box" href="{{ route('stock.index') }}"
            :current="request()->routeIs('stock.index')">
            Stock
        </flux:sidebar.item>

        <flux:sidebar.item icon="cube" href="{{ route('products.index') }}"
            :current="request()->routeIs('products.index')">
            Products
        </flux:sidebar.item>

        <flux:sidebar.group expandable heading="Products" class="grid">

            <flux:sidebar.item icon="squares-2x2" href="{{ route('product-categories.index') }}"
                :current="request()->routeIs('product-categories.index')">
                Product Categories
            </flux:sidebar.item>

            <flux:sidebar.item icon="arrows-right-left">Product Distribution</flux:sidebar.item>
            <flux:sidebar.item icon="gift" href="{{ route('product-rewards.index') }}"
                :current="request()->routeIs('product-rewards.index')">
                Product Rewards
            </flux:sidebar.item>
            <flux:sidebar.item icon="clock">Product Stock Histories</flux:sidebar.item>
        </flux:sidebar.group>


    </flux:sidebar.nav>

    <flux:sidebar.spacer />
</flux:sidebar>

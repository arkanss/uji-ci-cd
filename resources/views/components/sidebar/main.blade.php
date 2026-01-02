<flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.header>
        <flux:sidebar.brand
            href="#"
            logo="{{ asset('images/logo_black.png') }}"
            logo:dark="{{ asset('images/logo_white.svg') }}"
            name="PT. DAYA" />
        <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        @if(auth()->user()->canAccessMenu('dashboard'))
        <flux:sidebar.item icon="home" href="{{ route('dashboard') }}" :current="request()->routeIs('dashboard')">
            Dashboard
        </flux:sidebar.item>
        @endif

        <flux:sidebar.group expandable icon="banknotes" heading="Finance" class="grid">
            @if(auth()->user()->canAccessMenu('purchase-order'))
            <flux:sidebar.item href="{{ route('finance.purchase-order.index') }}"
                :current="request()->routeIs('finance.purchase-order*')">
                Purchase Orders
            </flux:sidebar.item>
            @endif

            @if(auth()->user()->canAccessMenu('operational-cost'))
            <flux:sidebar.item href="{{ route('finance.operational-cost.index') }}"
                :current="request()->routeIs('finance.operational-cost*')">
                Operational Costs
            </flux:sidebar.item>
            @endif
        </flux:sidebar.group>

        <flux:sidebar.group expandable icon="archive-box" heading="Inventory" class="grid">
            @if(auth()->user()->canAccessMenu('receive-goods'))
            <flux:sidebar.item href="{{ route('receive-po.index') }}"
                :current="request()->routeIs('receive-po.index')">
                Receive Goods
            </flux:sidebar.item>
            @endif

            @if(auth()->user()->canAccessMenu('stock'))
            <flux:sidebar.item href="{{ route('stock.index') }}"
                :current="request()->routeIs('stock.index')">
                Stocks
            </flux:sidebar.item>
            @endif
        </flux:sidebar.group>

        <flux:sidebar.group expandable icon="arrow-path" heading="Operational" class="grid">
            @if(auth()->user()->canAccessMenu('outlet-order'))
            <flux:sidebar.item href="{{ route('purchase-orders.index') }}"
                :current="request()->routeIs('purchase-orders.index')">
                Outlet Orders
            </flux:sidebar.item>
            @endif

            @if(auth()->user()->canAccessMenu('delivery'))
            <flux:sidebar.item href="{{ route('delivery.index') }}"
                :current="request()->routeIs('delivery.index')">
                Deliveries
            </flux:sidebar.item>
            @endif
        </flux:sidebar.group>

        {{--
        @if(auth()->user()->canAccessMenu('admin'))
        <flux:sidebar.item icon="users" href="{{ route('admin.index') }}" :current="request()->routeIs('admin.index')">
        Admin Management
        </flux:sidebar.item>
        @endif

        @if(auth()->user()->canAccessMenu('csv_reports'))
        <flux:sidebar.item icon="document-chart-bar" href="{{ route('csv-reports.index') }}"
            :current="request()->routeIs('csv-reports.index')">
            CSV Reports
        </flux:sidebar.item>
        @endif

        @if(auth()->user()->canAccessMenu('operational'))
        <flux:sidebar.group expandable heading="Operational" class="grid">
            @if(auth()->user()->canAccessMenu('dp_item_requests'))
            <flux:sidebar.item icon="clipboard-document-check" href="{{ route('dp-item-requests.index') }}"
                :current="request()->routeIs('dp-item-requests.index')">
                DP Item Requests
            </flux:sidebar.item>
            @endif

            @if(auth()->user()->canAccessMenu('delivery'))
            <flux:sidebar.item icon="truck" href="{{ route('delivery.index') }}"
                :current="request()->routeIs('delivery.index')">
                Delivery
            </flux:sidebar.item>
            @endif

            @if(auth()->user()->canAccessMenu('operational_costs'))
            <flux:sidebar.item icon="banknotes" href="{{ route('operational-costs.index') }}"
                :current="request()->routeIs('operational-costs.index')">
                Operational Cost
            </flux:sidebar.item>
            @endif

            @if(auth()->user()->canAccessMenu('point_management'))
            <flux:sidebar.item icon="star" href="{{ route('point-management.index') }}"
                :current="request()->routeIs('point-management.index')">
                Point Management
            </flux:sidebar.item>
            @endif
        </flux:sidebar.group>
        @endif

        @if(auth()->user()->canAccessMenu('receive_po'))
        <flux:sidebar.item icon="arrow-down-tray" href="{{ route('receive-po.index') }}"
            :current="request()->routeIs('receive-po.index')">
            Receive PO
        </flux:sidebar.item>
        @endif

        @if(auth()->user()->canAccessMenu('merchants'))
        <flux:sidebar.item icon="building-storefront" href="{{ route('merchants.index') }}"
            :current="request()->routeIs('merchants.index')">
            Merchants
        </flux:sidebar.item>
        @endif

        @if(auth()->user()->canAccessMenu('challenges'))
        <flux:sidebar.item icon="trophy" href="{{ route('challenges.index') }}"
            :current="request()->routeIs('challenges.index')">
            Challenge Management
        </flux:sidebar.item>
        @endif

        @if(auth()->user()->canAccessMenu('products'))
        <flux:sidebar.item icon="cube" href="{{ route('products.index') }}"
            :current="request()->routeIs('products.index')">
            Products
        </flux:sidebar.item>
        @endif

        @if(auth()->user()->canAccessMenu('product_categories') || auth()->user()->canAccessMenu('product_rewards') || auth()->user()->canAccessMenu('product_distribution') || auth()->user()->canAccessMenu('product_stock_history'))
        <flux:sidebar.group expandable heading="Products" class="grid">
            @if(auth()->user()->canAccessMenu('product_categories'))
            <flux:sidebar.item icon="squares-2x2" href="{{ route('product-categories.index') }}"
                :current="request()->routeIs('product-categories.index')">
                Product Categories
            </flux:sidebar.item>
            @endif

            @if(auth()->user()->canAccessMenu('product_rewards'))
            <flux:sidebar.item icon="gift" href="{{ route('product-rewards.index') }}"
                :current="request()->routeIs('product-rewards.index')">
                Product Rewards
            </flux:sidebar.item>
            @endif

            @if(auth()->user()->canAccessMenu('product_distribution'))
            <flux:sidebar.item icon="arrows-right-left" href="{{ route('product-distribution.index') }}"
                :current="request()->routeIs('product-distribution.index')">
                Product Distribution
            </flux:sidebar.item>
            @endif

            @if(auth()->user()->canAccessMenu('product_stock_history'))
            <flux:sidebar.item icon="clock" href="{{ route('product-stock-history.index') }}"
                :current="request()->routeIs('product-stock-history.index')">
                Product Stock Histories
            </flux:sidebar.item>
            @endif
        </flux:sidebar.group>
        @endif
        --}}
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        @php
        $user = auth('admin')->user(); // penting: guard admin
        $initial = $user ? strtoupper(substr($user->name, 0, 1)) : null;
        $avatar = $user?->avatar;
        @endphp
        <flux:sidebar.profile avatar="{{ $avatar }}" name="{{ auth()->user()->name ?? 'Administrator' }}" />
        <flux:menu>
            <flux:menu.item icon="arrow-right-start-on-rectangle" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>
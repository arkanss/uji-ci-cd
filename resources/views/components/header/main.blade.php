<flux:header class="border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
    <flux:spacer />

    {{-- Dark Mode Switcher ditaruh di sini --}}
    <flux:dropdown x-data align="end">
        <flux:button variant="subtle" square class="group" aria-label="Preferred color scheme">
            <flux:icon.sun x-show="$flux.appearance === 'light'" variant="mini" class="text-zinc-500 dark:text-white" />
            <flux:icon.moon x-show="$flux.appearance === 'dark'" variant="mini" class="text-zinc-500 dark:text-white" />
            <flux:icon.moon x-show="$flux.appearance === 'system' && $flux.dark" variant="mini" />
            <flux:icon.sun x-show="$flux.appearance === 'system' && ! $flux.dark" variant="mini" />
        </flux:button>

        <flux:menu>
            <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">Light</flux:menu.item>
            <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">Dark</flux:menu.item>
            <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">System</flux:menu.item>
        </flux:menu>
    </flux:dropdown>

    <flux:dropdown position="bottom" align="end">
        @php
            $user = auth('admin')->user(); // penting: guard admin
            $initial = $user ? strtoupper(substr($user->name, 0, 1)) : null;
            $avatar = $user?->avatar;
        @endphp

        <flux:profile avatar="{{ $avatar }}" name="{{ auth()->user()?->name }}">
            @if (!$avatar)
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-full bg-gray-400 text-white font-bold text-lg">
                    {{ $initial }}
                </div>
            @endif
        </flux:profile>

        <flux:menu>
            <flux:menu.item icon="user-circle">Profile</flux:menu.item>
            <flux:menu.item icon="cog-6-tooth">Settings</flux:menu.item>
            <flux:menu.separator />
            <flux:menu.item icon="arrow-right-start-on-rectangle"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Logout
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>
</flux:header>

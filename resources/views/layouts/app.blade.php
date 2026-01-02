<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? (View::hasSection('title') ? View::getSection('title') : 'Default') }} - {{ config('app.name', 'LocalPlace') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/icon-192x192.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="font-sans antialiased min-h-screen flex overflow-x-hidden bg-white dark:bg-zinc-800">

    <x-sidebar.main />

    <div class="flex-1 flex flex-col">

        <x-header.main :breadcrumbs="$breadcrumbs ?? []" />

        <main class="flex-1 bg-white dark:bg-zinc-800">
            @if (isset($slot))
            {{ $slot }}
            @else
            @yield('content')
            @endif

            @yield('scripts')
        </main>
    </div>

    @fluxScripts
    {{-- <script src="https://unpkg.com/html5-qrcode"></script> --}}
    <script src="https://unpkg.com/@zxing/library@latest"></script>
</body>


</html>
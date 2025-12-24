<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? (View::hasSection('title') ? View::getSection('title') : 'Default') }} - {{ config('app.name', 'LocalPlace') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="font-sans antialiased min-h-screen flex overflow-x-hidden bg-white dark:bg-zinc-800">

    <x-sidebar.main />

    <div class="flex-1 flex flex-col">

        <x-header.main />

        <main class="flex-1 p-6 bg-white dark:bg-zinc-800">
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>

    @fluxScripts
</body>


</html>

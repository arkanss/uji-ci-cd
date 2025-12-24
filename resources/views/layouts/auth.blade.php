<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="font-sans min-h-screen flex items-center justify-center bg-zinc-50 dark:bg-zinc-900">
    @yield('content')
    @fluxScripts
</body>
</html>

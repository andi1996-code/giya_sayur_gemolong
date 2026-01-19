<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-4">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">{{ config('app.name', 'POS') }}</h1>
            <p class="text-blue-100">Sistem Point of Sale</p>
        </div>

        <!-- Content -->
        @yield('content')

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-blue-100 text-sm">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi.
            </p>
        </div>
    </div>

    @livewireScripts
</body>
</html>

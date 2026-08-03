<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AnoWash — Marketing</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow px-6 py-3 flex justify-between items-center">
        <span class="font-semibold">AnoWash — Marketing</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-red-600">Keluar</button>
        </form>
    </nav>
    {{ $slot }}
    @livewireScripts
</body>
</html>
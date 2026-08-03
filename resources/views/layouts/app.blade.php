<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AnoWash</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">
    <nav
        x-data="{ mobileOpen: false, masterOpen: false }"
        class="bg-white shadow px-6 py-3 relative"
    >
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <span class="font-semibold">AnoWash</span>

                {{-- Menu desktop --}}
                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('app.dashboard') }}" class="text-sm text-gray-600 hover:text-teal-600">Dashboard</a>
                    <a href="{{ route('app.outlets') }}" class="text-sm text-gray-600 hover:text-teal-600">Outlet</a>

                    @if(auth()->user()->role === 'owner')
                    {{-- Dropdown: Master Data --}}
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button
                            @click="open = !open"
                            class="text-sm text-gray-600 hover:text-teal-600 flex items-center gap-1"
                        >
                            Master Data
                            <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-transition
                            x-cloak
                            class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg border py-2 z-40"
                        >
                            <a href="{{ route('app.staff') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-teal-600">Admin</a>
                            <a href="{{ route('app.allowance-types') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-teal-600">Tunjangan</a>
                            <a href="{{ route('app.employees') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-teal-600">Karyawan</a>
                            <a href="{{ route('app.vehicle-categories') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-teal-600">Kategori Kendaraan</a>
                            <a href="{{ route('app.services') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-teal-600">Layanan</a>
                            <a href="{{ route('app.pricing') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-teal-600">Harga Layanan</a>
                            <a href="{{ route('app.commission-rules') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-teal-600">Aturan Komisi</a>
                        </div>
                    </div>
                    @endif

                    <a href="{{ route('app.orders') }}" class="text-sm text-gray-600 hover:text-teal-600">Order</a>
                    <a href="{{ route('app.expenses') }}" class="text-sm text-gray-600 hover:text-teal-600">Pengeluaran</a>

                    @if(auth()->user()->role === 'owner')
                        <a href="{{ route('app.payroll') }}" class="text-sm text-gray-600 hover:text-teal-600">Payroll</a>
                        <a href="{{ route('app.reports') }}" class="text-sm text-gray-600 hover:text-teal-600">Laporan</a>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-4">
                {{-- Tombol keluar, tetap tampil di semua ukuran --}}
                <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                    @csrf
                    <button type="submit" class="text-sm text-red-600">Keluar</button>
                </form>

                {{-- Tombol hamburger, hanya muncul di mobile --}}
                <button
                    @click="mobileOpen = !mobileOpen"
                    class="md:hidden text-gray-600 focus:outline-none"
                    aria-label="Toggle menu"
                >
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Menu mobile --}}
        <div
            x-show="mobileOpen"
            x-transition
            x-cloak
            class="md:hidden mt-3 flex flex-col gap-1 pb-2"
        >
            <a href="{{ route('app.dashboard') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Dashboard</a>
            <a href="{{ route('app.outlets') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Outlet</a>

            {{-- Dropdown Master Data (mobile) --}}
            <button
                @click="masterOpen = !masterOpen"
                class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded flex items-center justify-between"
            >
                Master Data
                <svg class="w-3 h-3 transition-transform" :class="masterOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="masterOpen" x-transition x-cloak class="pl-4 flex flex-col gap-1">
                @if(auth()->user()->role === 'owner')
                    <a href="{{ route('app.staff') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Admin</a>
                    <a href="{{ route('app.employees') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Karyawan</a>
                    <a href="{{ route('app.commission-rules') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Aturan Komisi</a>
                    <a href="{{ route('app.allowance-types') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Tunjangan</a>
                @endif
                <a href="{{ route('app.vehicle-categories') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Kategori Kendaraan</a>
                <a href="{{ route('app.services') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Layanan</a>
                <a href="{{ route('app.pricing') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Harga Layanan</a>
            </div>

            <a href="{{ route('app.orders') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Order</a>
            <a href="{{ route('app.expenses') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Pengeluaran</a>

            @if(auth()->user()->role === 'owner')
                <a href="{{ route('app.payroll') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Payroll</a>
                <a href="{{ route('app.reports') }}" class="px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded">Laporan</a>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="pt-2 mt-2 border-t">
                @csrf
                <button type="submit" class="w-full text-left px-2 py-2 text-sm text-red-600">Keluar</button>
            </form>
        </div>
    </nav>

    {{ $slot }}

    <div
        x-data="{ show: false, message: '', type: 'success' }"
        x-on:notify.window="
            message = $event.detail.message;
            type = $event.detail.type || 'success';
            show = true;
            setTimeout(() => show = false, 4000);
        "
        x-show="show"
        x-transition
        class="fixed top-4 right-4 z-50 px-4 py-3 rounded shadow-lg text-white"
        :class="type === 'error' ? 'bg-red-600' : 'bg-teal-600'"
        x-cloak
    >
        <span x-text="message"></span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @livewireScripts
</body>
</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'WiFi Manager UMPKU') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased" x-data="{ sidebarOpen: true, navbarVisible: true }" @scroll.window="navbarVisible = (window.scrollY < 50)">

    {{-- BACKGROUND --}}
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('img/wp2.jpg') }}')"></div>
        <div class="absolute inset-0 bg-white/80"></div>
    </div>

    <div class="relative z-10 min-h-screen flex">

        {{-- SIDEBAR (orange theme) --}}
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="fixed inset-y-0 left-0 z-30 bg-gradient-to-b from-[#FF8C00] to-[#E65100] text-white transition-all duration-300 flex flex-col shadow-2xl">
            {{-- Logo --}}
            <div class="flex items-center gap-3 px-5 py-5 border-b border-white/20">
                <img src="{{ asset('img/logoputih.png') }}" alt="Logo UMPKU" class="w-10 h-10 rounded-lg object-contain flex-shrink-0">
                <div x-show="sidebarOpen" x-transition class="overflow-hidden">
                    <h1 class="text-sm font-bold leading-tight">WiFi Manager</h1>
                    <p class="text-[10px] text-white/70 leading-tight">UMPKU Surakarta</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <p x-show="sidebarOpen" class="px-3 text-[10px] uppercase tracking-wider text-white/50 mb-3">Menu Utama</p>

                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-white/25 text-white shadow-lg' : 'text-white/80 hover:bg-white/15 hover:text-white' }}">
                    <i class="fas fa-home w-5 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen">Dashboard</span>
                </a>

                <a href="{{ route('hotspot.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('hotspot.index') ? 'bg-white/25 text-white shadow-lg' : 'text-white/80 hover:bg-white/15 hover:text-white' }}">
                    <i class="fas fa-users w-5 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen">Kelola User</span>
                </a>

                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('profile.edit') ? 'bg-white/25 text-white shadow-lg' : 'text-white/80 hover:bg-white/15 hover:text-white' }}">
                    <i class="fas fa-cog w-5 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen">Pengaturan</span>
                </a>
            </nav>

            {{-- Sidebar Footer --}}
            <div class="px-3 py-4 border-t border-white/20">
                <button @click="sidebarOpen = !sidebarOpen" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/70 hover:text-white hover:bg-white/15 transition w-full">
                    <i :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'" class="fas w-5 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen">Tutup Sidebar</span>
                </button>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <div :class="sidebarOpen ? 'ml-64' : 'ml-20'" class="flex-1 transition-all duration-300 flex flex-col min-h-screen">

            {{-- TOP NAVBAR (only on dashboard, hides on scroll) --}}
            @if(request()->routeIs('dashboard'))
            <header x-show="navbarVisible" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-full" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-full" class="sticky top-0 z-20 bg-white/95 border-b border-black/10 shadow-[0_2px_15px_rgba(0,0,0,0.08)]">
                <div class="flex items-center justify-between px-6 py-3">
                    <div>
                        <h2 class="text-lg font-semibold text-[#1a1a2e]">@yield('page-title', 'Dashboard')</h2>
                        <p class="text-xs text-[#4a4a6a]">Universitas Muhammadiyah PKU Surakarta</p>
                    </div>

                    <div class="flex items-center gap-4">
                        {{-- User Dropdown --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm text-[#4a4a6a] hover:text-[#1a1a2e] transition">
                                <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white text-xs font-bold shadow-md">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="hidden sm:block font-medium">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-black/10 py-1 z-50">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-[#4a4a6a] hover:bg-gray-50 hover:text-[#1a1a2e] transition">
                                    <i class="fas fa-user-circle w-4"></i> Profil Saya
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2 w-full text-left px-4 py-2.5 text-sm text-primary hover:bg-red-50 transition">
                                        <i class="fas fa-sign-out-alt w-4"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            @endif

            {{-- FLASH MESSAGES --}}
            @if(session('success'))
            <div class="mx-6 mt-4 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mx-6 mt-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-primary text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
            @endif

            {{-- PAGE CONTENT --}}
            <main class="p-6 flex-1">
                {{ $slot }}
            </main>

            {{-- FOOTER (fixed bottom) --}}
            <footer class="bg-white/80 border-t border-gray-100 px-6 py-3 text-center mt-auto">
                <p class="text-gray-400 text-xs">&copy; {{ date('Y') }} WiFi Manager — UMPKU Surakarta</p>
            </footer>
        </div>

    </div>

</body>
</html>

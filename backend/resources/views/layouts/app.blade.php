<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Hotspot - UMPKU Surakarta</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logogram.png') }}" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50/80" x-data="{ sidebarOpen: window.innerWidth >= 768, mobileMenu: false, navbarVisible: true, lastScrollY: 0 }" @resize.window="sidebarOpen = window.innerWidth >= 768; if(window.innerWidth >= 768) mobileMenu = false" @scroll.window="const cy = window.scrollY; if(cy > lastScrollY && cy > 60) { navbarVisible = false } else { navbarVisible = true } lastScrollY = cy">

    {{-- BACKGROUND --}}
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('img/wp2.jpg') }}')"></div>
        <div class="absolute inset-0 bg-white/80"></div>
    </div>

    <div class="relative z-10 min-h-screen flex">

        {{-- MOBILE OVERLAY --}}
        <div x-show="mobileMenu" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="mobileMenu = false" class="fixed inset-0 bg-black/40 z-40 md:hidden" style="display:none"></div>

        {{-- SIDEBAR --}}
        <aside :class="[
            sidebarOpen ? 'w-60' : 'w-[68px]',
            mobileMenu ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        ]" class="fixed inset-y-0 left-0 z-50 bg-gradient-to-b from-[#FF8C00] to-[#E65100] text-white transition-all duration-300 flex flex-col shadow-2xl">
            {{-- Logo --}}
            <div class="flex items-center gap-3 px-4 py-4 border-b border-white/20">
                <img src="{{ asset('img/logoputih.png') }}" alt="Logo UMPKU" class="w-9 h-9 rounded-lg object-contain flex-shrink-0">
                <div x-show="sidebarOpen" x-transition class="overflow-hidden">
                    <h1 class="text-sm font-bold leading-tight">WiFi Manager</h1>
                    <p class="text-[10px] text-white/70 leading-tight">UMPKU Surakarta</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <p x-show="sidebarOpen" class="px-3 text-[10px] uppercase tracking-wider text-white/50 mb-3">Menu Utama</p>

                <a href="{{ route('dashboard') }}" @click="mobileMenu = false"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-white/25 text-white shadow-lg' : 'text-white/80 hover:bg-white/15 hover:text-white' }}">
                    <i class="fas fa-home w-5 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen">Dashboard</span>
                </a>

                @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.index') }}" @click="mobileMenu = false"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.*') && !request()->routeIs('admin.log_activities.index') ? 'bg-white/25 text-white shadow-lg' : 'text-white/80 hover:bg-white/15 hover:text-white' }}">
                    <i class="fas fa-user-shield w-5 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen">Manajemen Admin</span>
                </a>
                <a href="{{ route('admin.log_activities.index') }}" @click="mobileMenu = false"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.log_activities.index') ? 'bg-white/25 text-white shadow-lg' : 'text-white/80 hover:bg-white/15 hover:text-white' }}">
                    <i class="fas fa-list-check w-5 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen">Log Aktivitas</span>
                </a>
                @endif

                <a href="{{ route('hotspot.index') }}" @click="mobileMenu = false"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('hotspot.index') ? 'bg-white/25 text-white shadow-lg' : 'text-white/80 hover:bg-white/15 hover:text-white' }}">
                    <i class="fas fa-users w-5 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen">Manajemen User</span>
                </a>

                <a href="{{ route('hotspot.monitoring') }}" @click="mobileMenu = false"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('hotspot.monitoring') ? 'bg-white/25 text-white shadow-lg' : 'text-white/80 hover:bg-white/15 hover:text-white' }}">
                    <i class="fas fa-wifi w-5 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen">Monitoring User</span>
                </a>

                <a href="{{ route('profile.edit') }}" @click="mobileMenu = false"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('profile.edit') ? 'bg-white/25 text-white shadow-lg' : 'text-white/80 hover:bg-white/15 hover:text-white' }}">
                    <i class="fas fa-cog w-5 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen">Pengaturan</span>
                </a>
            </nav>

            {{-- Sidebar Toggle (desktop only) --}}
            <div class="hidden md:block px-3 py-3 border-t border-white/20">
                <button @click="sidebarOpen = !sidebarOpen" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/70 hover:text-white hover:bg-white/15 transition w-full">
                    <i :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'" class="fas w-5 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen">Tutup Sidebar</span>
                </button>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <div :class="sidebarOpen ? 'md:ml-60' : 'md:ml-[68px]'" class="flex-1 transition-all duration-300 flex flex-col min-h-screen w-full">

            {{-- TOP NAVBAR --}}
            {{-- Mobile: semua halaman (auto-hide on scroll) | Desktop: dashboard only --}}
            <header :class="navbarVisible ? 'translate-y-0' : '-translate-y-full'"
                    class="sticky top-0 z-20 bg-white/95 backdrop-blur-sm border-b border-orange-100/60 shadow-sm transition-transform duration-300 {{ request()->routeIs('dashboard') ? '' : 'md:hidden' }}">
                <div class="flex items-center justify-between px-3 md:px-5 py-2.5">
                    <div class="flex items-center gap-3">
                        <button @click="mobileMenu = !mobileMenu" class="md:hidden w-9 h-9 flex items-center justify-center rounded-lg text-gray-600 hover:bg-orange-50 active:bg-orange-100 transition">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div>
                            <h2 class="text-sm md:text-base font-semibold text-gray-800 leading-tight">@yield('page-title', 'Dashboard')</h2>
                            <p class="text-[10px] text-orange-400/70 hidden sm:block">UMPKU Surakarta</p>
                        </div>
                    </div>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition">
                            <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white text-xs font-bold shadow">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="hidden sm:block font-medium text-gray-700 text-sm max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down text-[10px] hidden sm:inline"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition">
                                <i class="fas fa-user-circle w-4"></i> Profil Saya
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-2 w-full text-left px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition">
                                    <i class="fas fa-sign-out-alt w-4"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- FLASH MESSAGES --}}
            @if(session('success'))
            <div class="mx-3 md:mx-5 mt-3 px-3 py-2.5 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-2">
                <i class="fas fa-check-circle flex-shrink-0"></i> <span>{{ session('success') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div class="mx-3 md:mx-5 mt-3 px-3 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle flex-shrink-0"></i> <span>{{ session('error') }}</span>
            </div>
            @endif

            {{-- PAGE CONTENT --}}
            <main class="p-3 md:p-5 flex-1">
                @yield('content')
            </main>

            {{-- FOOTER --}}
            <footer class="bg-white/60 border-t border-gray-100 px-4 py-2 text-center mt-auto">
                <p class="text-gray-400 text-[11px]">&copy; {{ date('Y') }} WiFi Manager — UMPKU Surakarta</p>
            </footer>
        </div>

    </div>

</body>
</html>

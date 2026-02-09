<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WiFi Manager UMPKU') }}</title>

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">

        {{-- WALLPAPER BACKGROUND --}}
        <div class="fixed inset-0 z-0">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('img/wp2.jpg') }}')"></div>
            <div class="absolute inset-0 bg-white/75"></div>
        </div>

        {{-- NAVBAR --}}
        <nav class="relative z-20 bg-white/95 border-b border-black/10 shadow-[0_2px_15px_rgba(0,0,0,0.08)]">
            <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
                <a href="/" class="flex items-center gap-3">
                    <img src="{{ asset('img/logoweb.png') }}" alt="UMPKU" class="h-10 w-auto">
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="px-5 py-2 text-sm font-medium text-[#1a1a2e] hover:text-[#FF8C00] transition flex items-center gap-2">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="{{ route('register') }}" class="px-5 py-2 text-sm font-semibold bg-gradient-to-r from-[#FF8C00] to-[#E65100] text-white rounded-lg hover:shadow-lg hover:shadow-orange-300/30 transition flex items-center gap-2">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </div>
            </div>
        </nav>

        {{-- MAIN CONTENT --}}
        <div class="relative z-10 min-h-[calc(100vh-120px)] flex items-center justify-center py-10 px-4">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>

        {{-- FOOTER --}}
        <footer class="relative z-10 bg-white px-6 py-4 text-center border-t border-gray-100">
            <p class="text-[#4a4a6a] text-xs">&copy; {{ date('Y') }} WiFi Manager — UMPKU Surakarta. All rights reserved.</p>
        </footer>

    </body>
</html>

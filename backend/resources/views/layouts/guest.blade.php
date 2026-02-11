@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'WiFi Manager UMPKU') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('img/logogram.png') }}" />

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
        <nav class="relative z-20 bg-white/95 border-b border-black/10 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2.5 flex justify-between items-center">
                <a href="/" class="flex items-center gap-3">
                    <img src="{{ asset('img/logoweb.png') }}" alt="UMPKU" class="h-8 sm:h-10 w-auto">
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="px-3 sm:px-5 py-2 text-sm font-medium text-gray-700 hover:text-[#FF8C00] transition flex items-center gap-1.5">
                        <i class="fas fa-sign-in-alt text-xs"></i> <span>Login</span>
                    </a>
                    <a href="{{ route('register') }}" class="px-3 sm:px-5 py-2 text-sm font-semibold bg-gradient-to-r from-[#FF8C00] to-[#E65100] text-white rounded-lg hover:shadow-lg transition flex items-center gap-1.5">
                        <i class="fas fa-user-plus text-xs"></i> <span class="hidden sm:inline">Register</span><span class="sm:hidden">Daftar</span>
                    </a>
                </div>
            </div>
        </nav>

        {{-- MAIN CONTENT --}}
        <div class="relative z-10 min-h-[calc(100vh-100px)] flex items-center justify-center py-6 sm:py-10 px-4">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>

        {{-- FOOTER --}}
        <footer class="relative z-10 bg-white/80 px-4 py-3 text-center border-t border-gray-100">
            <p class="text-gray-400 text-[11px]">&copy; {{ date('Y') }} WiFi Manager — UMPKU Surakarta</p>
        </footer>

    </body>
</html>

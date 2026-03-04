<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Daftar Hotspot - Mahasiswa - UMPKU Surakarta</title>
        <link rel="icon" type="image/png" href="{{ asset('img/logogram.png') }}" />

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">

        {{-- WALLPAPER BACKGROUND --}}
        <div class="fixed inset-0 z-0">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('img/wp2.jpg') }}')"></div>
            <div class="absolute inset-0 bg-white/75"></div>
        </div>

        {{-- MAIN CONTENT (tanpa navbar) --}}
        <div class="relative z-10 min-h-screen flex items-center justify-center py-10 px-4">
            <div class="w-full max-w-md">
                <div class="glass-card rounded-2xl p-8">
                    {{-- Logo --}}
                    <div class="text-center mb-8">
                        <img src="{{ asset('img/logotulisan.png') }}" alt="UMPKU" class="h-20 w-auto mx-auto">
                        <h1 class="mt-4 text-lg font-semibold text-[#1a1a2e]">Register Mahasiswa</h1>
                        <p class="text-xs text-[#4a4a6a]">UMPKU Surakarta - Mahasiswa</p>
                    </div>

                    {{-- NOTIFIKASI --}}
                    @if (session('success'))
                    <div class="mb-5 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-[#E53935] text-sm flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                    @endif

                    @if ($errors->any())
                    <div class="mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-sm">
                        <ul class="list-disc list-inside text-[#E53935]">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('hotspot.selfRegister') }}">
                        @csrf

                        {{-- Profile otomatis MahasiswaMagang --}}
                        <input type="hidden" name="profile" value="@mahasiswa">

                        <!-- Username -->
                        <div class="mb-5">
                            <label for="name" class="block text-sm font-medium text-[#1a1a2e] mb-2">Username</label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-[#4a4a6a]"></i>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                                       class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm text-[#1a1a2e] bg-white/90 focus:border-[#FBC02D] focus:ring-2 focus:ring-[#FBC02D]/30 transition placeholder:text-[#4a4a6a]"
                                       placeholder="Masukkan username">
                            </div>
                        </div>

                        <!-- Email / Akun Google -->
                        <div class="mb-5">
                            <label for="email" class="block text-sm font-medium text-[#1a1a2e] mb-2">Akun Google (Email)</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-[#4a4a6a]"></i>
                                <input id="email" type="email" name="email" value="{{ old('email') }}"
                                       class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm text-[#1a1a2e] bg-white/90 focus:border-[#FBC02D] focus:ring-2 focus:ring-[#FBC02D]/30 transition placeholder:text-[#4a4a6a]"
                                       placeholder="contoh@gmail.com (opsional)">
                            </div>
                            <p class="mt-1 text-xs text-[#4a4a6a]">Digunakan untuk login via Google di lain waktu</p>
                        </div>

                        <!-- Password -->
                        <div class="mb-6">
                            <label for="password" class="block text-sm font-medium text-[#1a1a2e] mb-2">Password</label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-[#4a4a6a]"></i>
                                <input id="password" type="password" name="password" required
                                       class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm text-[#1a1a2e] bg-white/90 focus:border-[#FBC02D] focus:ring-2 focus:ring-[#FBC02D]/30 transition placeholder:text-[#4a4a6a]"
                                       placeholder="Buat password">
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-[#FF8C00] to-[#E65100] text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-orange-300/30 transition-all duration-300 flex items-center justify-center gap-2">
                            <i class="fas fa-wifi"></i> Daftar Hotspot
                        </button>
                    </form>

                    {{-- Divider --}}
                    <div class="flex items-center my-5">
                        <div class="flex-1 border-t border-gray-300"></div>
                        <span class="px-3 text-xs text-[#4a4a6a]">atau</span>
                        <div class="flex-1 border-t border-gray-300"></div>
                    </div>

                    {{-- Daftar dengan Google --}}
                    <a href="{{ route('hotspot.googleRegister', ['profile' => '@mahasiswa']) }}"
                       class="w-full py-3 bg-white border-2 border-gray-200 text-[#1a1a2e] font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 hover:shadow-md transition-all duration-300 flex items-center justify-center gap-3">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Daftar dengan Google
                    </a>
                </div>
            </div>
        </div>

    </body>
</html>

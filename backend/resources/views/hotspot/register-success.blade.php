<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Registrasi Berhasil - UMPKU Surakarta</title>
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

        {{-- MAIN CONTENT --}}
        <div class="relative z-10 min-h-screen flex items-center justify-center py-10 px-4">
            <div class="w-full max-w-md">
                <div class="glass-card rounded-2xl p-8">
                    {{-- Logo --}}
                    <div class="text-center mb-6">
                        <img src="{{ asset('img/logotulisan.png') }}" alt="UMPKU" class="h-20 w-auto mx-auto">
                    </div>

                    {{-- Success Icon --}}
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                            <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                        </div>
                        <h1 class="text-xl font-bold text-[#1a1a2e]">Registrasi Berhasil!</h1>
                        <p class="text-sm text-[#4a4a6a] mt-1">Akun hotspot Anda telah dibuat via Google</p>
                    </div>

                    {{-- User Info --}}
                    <div class="bg-gray-50 rounded-xl p-5 mb-6 space-y-3">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-user text-[#FF8C00] w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-[#4a4a6a]">Nama</p>
                                <p class="text-sm font-semibold text-[#1a1a2e]">{{ session('reg_name') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-envelope text-[#FF8C00] w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-[#4a4a6a]">Email Google</p>
                                <p class="text-sm font-semibold text-[#1a1a2e]">{{ session('reg_email') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-id-badge text-[#FF8C00] w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-[#4a4a6a]">Username Hotspot</p>
                                <p class="text-sm font-semibold text-[#1a1a2e]">{{ session('reg_username') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-key text-[#FF8C00] w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-[#4a4a6a]">Password</p>
                                <p class="text-sm font-semibold text-[#1a1a2e] font-mono">{{ session('reg_password') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-wifi text-[#FF8C00] w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-[#4a4a6a]">Profile</p>
                                <p class="text-sm font-semibold text-[#1a1a2e]">{{ session('reg_profile') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Warning --}}
                    <div class="mb-6 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs flex items-start gap-2">
                        <i class="fas fa-exclamation-triangle mt-0.5"></i>
                        <div>
                            <strong>Penting!</strong> Simpan username & password di atas. Anda bisa login hotspot menggunakan kredensial ini atau langsung klik tombol Google di halaman login hotspot.
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="space-y-3">
                        <a href="{{ route('hotspot.login') }}"
                           class="w-full py-3 bg-gradient-to-r from-[#FF8C00] to-[#E65100] text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-orange-300/30 transition-all duration-300 flex items-center justify-center gap-2">
                            <i class="fas fa-sign-in-alt"></i> Login Hotspot
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>

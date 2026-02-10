<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Daftar Hotspot - Tamu - UMPKU Surakarta</title>

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
                        <h1 class="mt-4 text-lg font-semibold text-[#1a1a2e]">Register Tamu</h1>
                        <p class="text-xs text-[#4a4a6a]">UMPKU Surakarta - Tamu</p>
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

                        {{-- Profile otomatis TamuMagang --}}
                        <input type="hidden" name="profile" value="TamuMagang">

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
                </div>
            </div>
        </div>

    </body>
</html>

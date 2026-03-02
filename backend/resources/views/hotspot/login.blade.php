<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login WiFi — UMPKU Surakarta</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logogram.png') }}" />

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Poppins', sans-serif; }
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 14px 24px;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            color: #1a1a2e;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .google-btn:hover {
            border-color: #4285f4;
            background: #f8faff;
            box-shadow: 0 4px 20px rgba(66, 133, 244, 0.15);
            transform: translateY(-1px);
        }
        .google-btn:active {
            transform: translateY(0);
        }
        .google-btn svg {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
        }
        .wifi-pulse {
            animation: wifi-pulse 2s ease-in-out infinite;
        }
        @keyframes wifi-pulse {
            0%, 100% { opacity: 0.6; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }
    </style>
</head>
<body class="antialiased">

    {{-- WALLPAPER BACKGROUND --}}
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('img/wp2.jpg') }}')"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-white/80 via-white/70 to-white/90"></div>
    </div>

    {{-- NAVBAR --}}
    <nav class="relative z-20 bg-white/95 border-b border-black/10 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2.5 flex justify-between items-center">
            <a href="/hotspot/login" class="flex items-center gap-3">
                <img src="{{ asset('img/logoweb.png') }}" alt="UMPKU" class="h-8 sm:h-10 w-auto">
            </a>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <i class="fas fa-wifi text-[#FF8C00]"></i>
                <span class="hidden sm:inline font-medium">WiFi Hotspot</span>
            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <div class="relative z-10 min-h-[calc(100vh-100px)] flex items-center justify-center py-6 sm:py-10 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 p-6 sm:p-8">

                {{-- WiFi Icon --}}
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-[#FF8C00] to-[#E65100] text-white mb-4 wifi-pulse">
                        <i class="fas fa-wifi text-2xl"></i>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Login WiFi Hotspot</h1>
                    <p class="text-sm text-gray-500 mt-1">UMPKU Surakarta</p>
                </div>

                {{-- Info Box --}}
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                        <div class="text-xs text-blue-700 leading-relaxed">
                            <p class="font-semibold mb-1">Petunjuk Login:</p>
                            <p>Klik tombol <strong>"Login dengan Google"</strong> di bawah. Sistem akan mencocokkan email Google Anda dengan akun WiFi yang sudah terdaftar.</p>
                        </div>
                    </div>
                </div>

                {{-- Error Message --}}
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
                            <div class="text-sm text-red-700">
                                {{ session('error') }}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Google Login Button --}}
                <a href="{{ route('auth.google') }}" class="google-btn">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Login dengan Google
                </a>

                {{-- Divider --}}
                <div class="flex items-center gap-3 my-6">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-xs text-gray-400 font-medium">atau daftar akun WiFi</span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                {{-- Self Register Links --}}
                <div class="grid grid-cols-2 gap-2">
                    <a href="/register-hotspot/dosen" class="flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:bg-orange-50 hover:border-orange-200 hover:text-orange-700 transition">
                        <i class="fas fa-chalkboard-teacher text-[10px]"></i> Dosen
                    </a>
                    <a href="/register-hotspot/mahasiswa" class="flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:bg-orange-50 hover:border-orange-200 hover:text-orange-700 transition">
                        <i class="fas fa-user-graduate text-[10px]"></i> Mahasiswa
                    </a>
                    <a href="/register-hotspot/staff" class="flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:bg-orange-50 hover:border-orange-200 hover:text-orange-700 transition">
                        <i class="fas fa-id-badge text-[10px]"></i> Staff
                    </a>
                    <a href="/register-hotspot/tamu" class="flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:bg-orange-50 hover:border-orange-200 hover:text-orange-700 transition">
                        <i class="fas fa-user-tag text-[10px]"></i> Tamu
                    </a>
                </div>
            </div>

            {{-- Admin Login Link --}}
            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-xs text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-lock text-[9px] mr-1"></i> Admin Panel
                </a>
            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <footer class="relative z-10 bg-white/80 px-4 py-3 text-center border-t border-gray-100">
        <p class="text-gray-400 text-[11px]">&copy; {{ date('Y') }} WiFi Manager — UMPKU Surakarta</p>
    </footer>

</body>
</html>

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

        .role-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 24px 16px;
            border-radius: 18px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1.5px solid transparent;
        }
        .role-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        .role-card:active { transform: translateY(0); }

        .role-card .role-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: transform 0.3s ease;
        }
        .role-card:hover .role-icon { transform: scale(1.1); }

        .card-blue    { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .card-blue .role-icon    { background: #dbeafe; color: #2563eb; }
        .card-emerald { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
        .card-emerald .role-icon { background: #d1fae5; color: #059669; }
        .card-purple  { background: #f5f3ff; color: #6d28d9; border-color: #c4b5fd; }
        .card-purple .role-icon  { background: #ede9fe; color: #7c3aed; }
        .card-amber   { background: #fffbeb; color: #b45309; border-color: #fcd34d; }
        .card-amber .role-icon   { background: #fef3c7; color: #d97706; }

        .wifi-pulse {
            animation: wifi-pulse 2s ease-in-out infinite;
        }
        @keyframes wifi-pulse {
            0%, 100% { opacity: 0.7; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.06); }
        }
        .fade-up { animation: fadeUp 0.5s ease-out both; }
        .fade-up-delay { animation-delay: 0.15s; }
        .fade-up-delay-2 { animation-delay: 0.3s; }
        .fade-up-delay-3 { animation-delay: 0.45s; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .glass-card {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.6);
        }
    </style>
</head>
<body class="antialiased">

    {{-- WALLPAPER BACKGROUND --}}
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('img/wp2.jpg') }}')"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-white/85 via-orange-50/60 to-white/90"></div>
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
            <div class="glass-card rounded-3xl shadow-2xl p-6 sm:p-8 fade-up">

                {{-- WiFi Icon & Title --}}
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-br from-[#FF8C00] to-[#E65100] text-white mb-4 wifi-pulse shadow-lg shadow-orange-200" style="width:72px;height:72px;">
                        <i class="fas fa-wifi text-3xl"></i>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 tracking-tight">Login WiFi Hotspot</h1>
                    <p class="text-sm text-gray-500 mt-1">UMPKU Surakarta</p>
                </div>

                {{-- Error Message --}}
                @if(session('error'))
                    <div class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200/80 rounded-2xl p-4 mb-6 fade-up fade-up-delay">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                                <i class="fas fa-exclamation text-red-500 text-xs"></i>
                            </div>
                            <div class="text-sm text-red-700 font-medium">
                                {{ session('error') }}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Instruction --}}
                <div class="text-center mb-5 fade-up fade-up-delay">
                    <p class="text-xs text-gray-500">Pilih jaringan WiFi yang Anda gunakan</p>
                </div>

                {{-- Role Selection Grid --}}
                <div class="grid grid-cols-2 gap-3 fade-up fade-up-delay-2">
                    <a href="/hotspot/login/dosen" class="role-card card-blue">
                        <div class="role-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <span>Dosen</span>
                    </a>
                    <a href="/hotspot/login/mahasiswa" class="role-card card-emerald">
                        <div class="role-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <span>Mahasiswa</span>
                    </a>
                    <a href="/hotspot/login/staff" class="role-card card-purple">
                        <div class="role-icon">
                            <i class="fas fa-id-badge"></i>
                        </div>
                        <span>Staff</span>
                    </a>
                    <a href="/hotspot/login/tamu" class="role-card card-amber">
                        <div class="role-icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <span>Tamu</span>
                    </a>
                </div>
            </div>

            {{-- Admin Login Link --}}
            <div class="text-center mt-5 fade-up fade-up-delay-3">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-lock text-[9px]"></i> Admin Panel
                </a>
            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <footer class="relative z-10 bg-white/80 backdrop-blur-sm px-4 py-3 text-center border-t border-gray-100">
        <p class="text-gray-400 text-[11px]">&copy; {{ date('Y') }} WiFi Manager — UMPKU Surakarta</p>
    </footer>

</body>
</html>

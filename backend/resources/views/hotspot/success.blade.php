<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login Berhasil — WiFi UMPKU</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logogram.png') }}" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Poppins', sans-serif; }
        .success-check {
            animation: check-bounce 0.6s ease-out;
        }
        @keyframes check-bounce {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }
        .fade-in {
            animation: fade-in 0.5s ease-out 0.3s both;
        }
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
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
                <i class="fas fa-wifi text-green-500"></i>
                <span class="hidden sm:inline font-medium text-green-600">Terhubung</span>
            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <div class="relative z-10 min-h-[calc(100vh-100px)] flex items-center justify-center py-6 sm:py-10 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 p-6 sm:p-8">

                {{-- Success Icon --}}
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-green-400 to-green-600 text-white mb-4 success-check">
                        <i class="fas fa-check text-3xl"></i>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Login Berhasil!</h1>
                    <p class="text-sm text-gray-500 mt-1">Anda sekarang terhubung ke WiFi UMPKU</p>
                </div>

                {{-- User Info --}}
                <div class="fade-in bg-green-50 border border-green-100 rounded-xl p-4 mb-6">
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user text-green-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-green-600 font-medium uppercase tracking-wider">Nama</p>
                                <p class="text-sm font-semibold text-gray-800">{{ session('google_name', '-') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-envelope text-green-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-green-600 font-medium uppercase tracking-wider">Email</p>
                                <p class="text-sm font-semibold text-gray-800">{{ session('email', '-') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-id-badge text-green-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-green-600 font-medium uppercase tracking-wider">Username Hotspot</p>
                                <p class="text-sm font-semibold text-gray-800">{{ session('username', '-') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-layer-group text-green-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-green-600 font-medium uppercase tracking-wider">Profile</p>
                                <p class="text-sm font-semibold text-gray-800">{{ session('profile', 'default') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status koneksi MikroTik --}}
                <div id="connecting-status" class="fade-in bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-spinner spinner text-blue-500 mt-0.5"></i>
                        <div class="text-xs text-blue-700 leading-relaxed">
                            <p class="font-semibold">Menghubungkan ke internet...</p>
                            <p>Sedang mengautentikasi ke MikroTik hotspot.</p>
                        </div>
                    </div>
                </div>

                <div id="connected-status" class="fade-in bg-green-50 border border-green-100 rounded-xl p-4 mb-6" style="display:none;">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                        <div class="text-xs text-green-700 leading-relaxed">
                            <p class="font-semibold">Internet aktif!</p>
                            <p>Koneksi internet Anda sudah aktif. Anda dapat menutup halaman ini dan mulai browsing.</p>
                        </div>
                    </div>
                </div>

                <div id="fallback-status" class="fade-in bg-amber-50 border border-amber-100 rounded-xl p-4 mb-6" style="display:none;">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                        <div class="text-xs text-amber-700 leading-relaxed">
                            <p class="font-semibold">Koneksi diaktifkan</p>
                            <p>Jika internet belum aktif, tunggu beberapa detik atau coba buka halaman baru.</p>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="fade-in space-y-3">
                    @if(session('link_orig'))
                        <a href="{{ session('link_orig') }}" class="block w-full py-3 bg-gradient-to-r from-[#FF8C00] to-[#E65100] text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-orange-300/30 transition-all duration-300 text-center text-sm">
                            <i class="fas fa-globe mr-2"></i> Lanjutkan Browsing
                        </a>
                    @endif
                    <a href="https://www.google.com" class="block w-full py-3 bg-gray-100 text-gray-600 font-semibold rounded-xl hover:bg-gray-200 transition-all duration-300 text-center text-sm">
                        <i class="fas fa-search mr-2"></i> Buka Google
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <footer class="relative z-10 bg-white/80 px-4 py-3 text-center border-t border-gray-100">
        <p class="text-gray-400 text-[11px]">&copy; {{ date('Y') }} WiFi Manager — UMPKU Surakarta</p>
    </footer>

    {{--
        AUTO-LOGIN KE MIKROTIK HOTSPOT

        Strategi autentikasi (berurutan):
        1. IP-binding bypass (sudah dilakukan server-side di controller)
           → Jika MAC address tersedia, user langsung bypass hotspot
        2. Cek konektivitas internet setelah 2 detik
           → Jika IP-binding berhasil, internet sudah aktif
        3. Fallback: redirect ke MikroTik login URL via GET (full-page navigation)
           → Tidak terkena mixed-content blocking (HTTPS→HTTP navigation diizinkan browser)
           → MikroTik memproses login dan redirect ke dst
    --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var linkLogin = @json(session('link_login', ''));
            var username  = @json(session('username', ''));
            var password  = @json(session('password', ''));
            var linkOrig  = @json(session('link_orig', ''));
            var hasCredentials = (linkLogin && username && password);

            /**
             * Cek apakah internet sudah aktif (IP-binding berhasil)
             * Menggunakan <img> ke external URL. Jika load → internet aktif.
             */
            function checkInternet(timeout) {
                return new Promise(function(resolve) {
                    var done = false;
                    var img = new Image();
                    img.onload = function() { if (!done) { done = true; resolve(true); } };
                    img.onerror = function() { if (!done) { done = true; resolve(false); } };
                    // Gunakan favicon Google (kecil, ada CORS header, reliable)
                    img.src = 'https://www.google.com/favicon.ico?_t=' + Date.now();
                    setTimeout(function() { if (!done) { done = true; resolve(false); } }, timeout || 5000);
                });
            }

            /**
             * Redirect ke MikroTik login URL via GET (full-page navigation).
             * Browser mengizinkan navigasi HTTPS → HTTP, jadi ini tidak terkena mixed-content block.
             */
            function redirectToMikrotikLogin() {
                if (!hasCredentials) return false;

                var url = linkLogin;
                url += (url.indexOf('?') >= 0 ? '&' : '?');
                url += 'username=' + encodeURIComponent(username);
                url += '&password=' + encodeURIComponent(password);
                if (linkOrig) {
                    url += '&dst=' + encodeURIComponent(linkOrig);
                }

                // Update UI sebelum redirect
                document.getElementById('connecting-status').innerHTML =
                    '<div class="flex items-start gap-3">' +
                    '<i class="fas fa-external-link-alt text-blue-500 mt-0.5"></i>' +
                    '<div class="text-xs text-blue-700 leading-relaxed">' +
                    '<p class="font-semibold">Mengaktifkan koneksi internet...</p>' +
                    '<p>Anda akan dialihkan ke gateway MikroTik untuk autentikasi.</p>' +
                    '</div></div>';

                setTimeout(function() {
                    window.location.href = url;
                }, 1000);
                return true;
            }

            // --- MAIN FLOW ---
            // Tunggu 2.5 detik agar IP-binding sempat aktif di MikroTik
            setTimeout(function() {
                checkInternet(4000).then(function(online) {
                    if (online) {
                        // IP-binding berhasil! Internet sudah aktif.
                        document.getElementById('connecting-status').style.display = 'none';
                        document.getElementById('connected-status').style.display = 'block';
                    } else if (hasCredentials) {
                        // Internet belum aktif — redirect ke MikroTik login URL
                        redirectToMikrotikLogin();
                    } else {
                        // Tidak ada link_login (bukan dari captive portal)
                        document.getElementById('connecting-status').style.display = 'none';
                        document.getElementById('fallback-status').style.display = 'block';
                    }
                });
            }, 2500);
        });
    </script>

</body>
</html>

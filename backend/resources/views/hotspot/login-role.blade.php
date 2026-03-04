<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="-1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>WiFi Hotspot - UMPKU Surakarta | {{ $roleData['label'] }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logogram.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @php
        $roleColors = [
            'dosen'     => ['primary' => '#1565C0', 'secondary' => '#42A5F5', 'accent' => '#90CAF9', 'gradient' => 'linear-gradient(90deg, #1565C0, #42A5F5)'],
            'mahasiswa' => ['primary' => '#E53935', 'secondary' => '#FB8C00', 'accent' => '#FBC02D', 'gradient' => 'linear-gradient(90deg, #f59e0b, #e85d04)'],
            'staff'     => ['primary' => '#6A1B9A', 'secondary' => '#AB47BC', 'accent' => '#CE93D8', 'gradient' => 'linear-gradient(90deg, #7c3aed, #a855f7)'],
            'tamu'      => ['primary' => '#E65100', 'secondary' => '#FF9800', 'accent' => '#FFB74D', 'gradient' => 'linear-gradient(90deg, #f59e0b, #e85d04)'],
        ];
        $rc = $roleColors[$role] ?? $roleColors['tamu'];
    @endphp

    <style>
        :root {
            --primary: {{ $rc['primary'] }};
            --secondary: {{ $rc['secondary'] }};
            --accent: {{ $rc['accent'] }};
            --text-dark: #1a1a2e;
            --text-light: #4a4a6a;
            --white: #ffffff;
            --error: #f44336;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        a { text-decoration: none; color: inherit; }

        /* Wallpaper Background */
        .wallpaper-bg {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; z-index: -2;
        }
        .wallpaper-bg .slide {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%;
            background-size: cover; background-position: center;
            opacity: 0; transition: opacity 1.5s ease-in-out;
        }
        .wallpaper-bg .slide:nth-child(1) {
            background-image: url('{{ asset('img/wp1.jpg') }}');
            opacity: 1;
        }
        .wallpaper-bg .slide:nth-child(2) {
            background-image: url('{{ asset('img/wp2.jpg') }}');
            opacity: 0;
        }
        .overlay-bg {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.75);
            z-index: -1;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 50px;
            display: flex; justify-content: space-between; align-items: center;
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }
        .navbar-brand { display: flex; align-items: center; }
        .navbar-brand img { height: 45px; width: auto; }
        .nav-menu { display: flex; align-items: center; gap: 10px; }
        .nav-link {
            color: var(--text-dark); padding: 10px 25px;
            font-weight: 500; font-size: 14px;
            transition: all 0.3s ease;
            display: flex; align-items: center; gap: 8px;
            position: relative;
        }
        .nav-link::after {
            content: ''; position: absolute;
            bottom: 5px; left: 25px; right: 25px;
            height: 2px; background: var(--accent);
            transform: scaleX(0); transition: transform 0.3s ease;
        }
        .nav-link:hover::after { transform: scaleX(1); }
        .nav-link.active { color: var(--primary); }
        .nav-link.active::after { transform: scaleX(1); }

        /* Page Section */
        .page-section {
            min-height: 100vh;
            display: flex; justify-content: center; align-items: center;
            padding: 100px 50px 50px;
            position: relative; z-index: 1;
        }

        /* Login Wrapper */
        .login-wrapper {
            display: flex; align-items: center; justify-content: center;
            gap: 80px; max-width: 1100px; width: 100%;
        }
        .logo-side { flex: 1; text-align: center; }
        .logo-side .main-logo {
            width: 380px; max-width: 90%; height: auto;
            margin-bottom: 20px;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.3));
        }
        .logo-side .tagline {
            font-size: 24px; font-weight: 600; font-style: italic;
            color: var(--accent);
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
            letter-spacing: 2px;
        }

        /* Login Container */
        .login-container {
            max-width: 420px; width: 100%;
            background: #ffffff; border-radius: 18px;
            padding: 28px 26px 26px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }
        .login-header { text-align: center; margin-bottom: 16px; }
        .login-header h2 {
            font-size: 16px; font-weight: 600;
            color: var(--text-dark); margin-bottom: 2px;
        }
        .login-header p { font-size: 12px; color: var(--text-light); }

        /* Buttons */
        .btn-register {
            display: block; width: 100%; padding: 12px;
            background: transparent;
            border: 2px solid var(--primary); border-radius: 8px;
            color: var(--primary);
            font-size: 14px; font-weight: 600;
            font-family: 'Poppins', sans-serif;
            text-align: center; margin-top: 12px;
        }
        .btn-register:hover { background: var(--primary); color: #fff; }

        /* Google Auth Button */
        .google-auth-wrapper { text-align: center; margin-top: 12px; }
        .btn-google {
            display: inline-flex; align-items: center; justify-content: center;
            width: 100%; padding: 12px 16px;
            background: #ffffff; color: #444444;
            border: 1px solid #dadce0; border-radius: 8px;
            font-size: 15px; font-weight: 500;
            font-family: 'Poppins', sans-serif;
            cursor: pointer; transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .btn-google svg {
            width: 20px; height: 20px;
            vertical-align: middle; margin-right: 10px; flex-shrink: 0;
        }
        .btn-google:hover {
            background: #f7f8f8; border-color: #c6c6c6;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        /* Error Message */
        .error-message {
            background: rgba(244, 67, 54, 0.1);
            border-radius: 10px;
            padding: 12px 15px; margin-top: 20px;
            display: flex; align-items: center; gap: 10px;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }
        .error-message i { color: var(--error); font-size: 18px; }
        .error-message span { color: #b91c1c; font-size: 13px; font-weight: 500; }

        /* Footer & Links */
        .footer-link { text-align: center; margin-top: 20px; }
        .footer-link a { color: var(--text-light); font-size: 13px; font-weight: 500; }
        .footer-link a:hover { color: var(--primary); text-decoration: underline; }
        .back-link { text-align: center; margin-top: 12px; }
        .back-link a {
            color: var(--text-light); font-size: 12px; font-weight: 500;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .back-link a:hover { color: var(--primary); }
        .main-footer {
            background: var(--text-dark); padding: 20px; text-align: center;
        }
        .main-footer p { color: rgba(255, 255, 255, 0.7); font-size: 13px; }

        /* Contact Section */
        #contact { background: rgba(255, 255, 255, 0.95); min-height: 100vh; }
        .contact-wrapper { max-width: 1100px; width: 100%; }
        .contact-header { text-align: center; margin-bottom: 50px; }
        .contact-header h2 { font-size: 36px; font-weight: 700; color: var(--text-dark); margin-bottom: 10px; }
        .contact-header p { color: var(--text-light); font-size: 16px; }
        .contact-content { display: flex; gap: 50px; align-items: stretch; }
        .contact-map { flex: 1; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); min-height: 400px; }
        .contact-info { flex: 1; display: flex; flex-direction: column; gap: 25px; }
        .contact-card {
            background: rgba(255, 255, 255, 0.98); border-radius: 15px; padding: 25px;
            display: flex; align-items: flex-start; gap: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid rgba(0, 0, 0, 0.08);
        }
        .contact-card:hover { border-color: var(--accent); }
        .contact-icon {
            width: 55px; height: 55px; background: var(--primary); border-radius: 12px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .contact-details h4 { font-size: 16px; font-weight: 600; color: var(--text-dark); margin-bottom: 5px; }
        .contact-details p { font-size: 14px; color: var(--text-light); line-height: 1.6; }
        .social-section { margin-top: 30px; padding-top: 30px; border-top: 1px solid #e5e7eb; }
        .social-links { display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; }
        .social-link {
            display: flex; align-items: center; gap: 8px; padding: 10px 20px;
            background: #f8f9fa; border-radius: 8px; color: var(--text-dark);
            font-size: 13px; font-weight: 500; transition: all 0.3s; border-bottom: 2px solid transparent;
        }
        .social-link:hover { background: #f0f0f0; border-bottom-color: var(--accent); }

        /* Responsive - Tablet */
        @media (max-width: 900px) {
            .navbar { padding: 12px 20px; }
            .page-section { padding: 100px 20px 50px; }
            .login-wrapper { flex-direction: column; gap: 25px; padding: 10px; }
            .logo-side { order: 1; }
            .logo-side .main-logo { width: 180px !important; height: auto !important; }
            .logo-side .tagline { font-size: 14px; letter-spacing: 1px; }
            .login-container { order: 2; max-width: 100%; padding: 25px 20px; }
            .login-header h2 { font-size: 22px; }
            #contact { padding: 80px 15px 25px; min-height: auto; }
            .contact-header { margin-bottom: 20px; }
            .contact-header h2 { font-size: 22px; color: var(--primary); }
            .contact-header p { font-size: 12px; }
            .contact-content { flex-direction: column; gap: 18px; }
            .contact-map { min-height: 160px; max-height: 180px; border-radius: 12px; }
            .contact-info { gap: 12px; }
            .contact-card { padding: 14px 16px; gap: 14px; border-radius: 12px; }
            .contact-icon { width: 44px; height: 44px; min-width: 44px; border-radius: 12px; }
            .contact-icon img { width: 22px !important; height: 22px !important; filter: brightness(0) invert(1); }
            .contact-details h4 { font-size: 14px; margin-bottom: 3px; }
            .contact-details p { font-size: 12px; line-height: 1.5; }
            .social-section { margin-top: 18px; padding-top: 18px; }
            .social-links { gap: 8px; }
            .social-link { padding: 8px 16px; font-size: 11px; border-radius: 20px; background: #f5f5f5; }
            .main-footer { padding: 15px 10px; }
            .main-footer p { font-size: 11px; }
        }

        /* Responsive - Mobile */
        @media (max-width: 480px) {
            .navbar { padding: 8px 12px; }
            .navbar-brand img { height: 32px; }
            .nav-link { padding: 6px 10px; font-size: 11px; gap: 4px; }
            .nav-link span { display: none; }
            #home { padding-top: 70px; min-height: 100vh; }
            #contact { padding: 70px 10px 30px; }
            .login-wrapper { gap: 15px; padding: 5px; }
            .logo-side .main-logo { width: 140px !important; }
            .logo-side .tagline { font-size: 12px; margin-top: -5px; }
            .login-container { padding: 20px 18px; }
            .login-header h2 { font-size: 20px; }
            .error-message { padding: 10px 12px; margin-top: 15px; }
            .error-message span { font-size: 12px; }
            .footer-link { margin-top: 15px; }
            .footer-link a { font-size: 12px; }
            .contact-header h2 { font-size: 20px; }
            .contact-header p { font-size: 11px; }
            .contact-content { gap: 14px; }
            .contact-map { min-height: 140px; max-height: 160px; border-radius: 10px; }
            .contact-card { padding: 12px 14px; gap: 12px; border-radius: 10px; }
            .contact-icon { width: 40px; height: 40px; min-width: 40px; border-radius: 10px; }
            .contact-icon img { width: 20px !important; height: 20px !important; filter: brightness(0) invert(1); }
            .contact-details h4 { font-size: 13px; margin-bottom: 2px; }
            .contact-details p { font-size: 11px; line-height: 1.4; }
            .contact-details p br { display: none; }
            .social-link { padding: 7px 14px; font-size: 10px; border-radius: 18px; }
            .main-footer { padding: 12px 8px; }
            .main-footer p { font-size: 10px; }
        }

        @media (max-width: 360px) {
            .logo-side .main-logo { width: 120px !important; }
            .logo-side .tagline { font-size: 10px; }
            .login-container { padding: 18px 15px; }
            .login-header h2 { font-size: 18px; }
            .contact-header h2 { font-size: 18px; }
            .contact-header p { font-size: 10px; }
            .contact-map { min-height: 120px; max-height: 140px; }
            .contact-card { padding: 10px 12px; gap: 10px; }
            .contact-icon { width: 36px; height: 36px; min-width: 36px; }
            .contact-details h4 { font-size: 12px; }
            .contact-details p { font-size: 10px; }
            .social-link { padding: 6px 12px; font-size: 9px; }
        }
    </style>
</head>

<body>
    <!-- Wallpaper Background -->
    <div class="wallpaper-bg">
        <div class="slide"></div>
        <div class="slide"></div>
    </div>
    <div class="overlay-bg"></div>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="/hotspot/login" class="navbar-brand">
            <img src="{{ asset('img/logo_web_umpku_color.png') }}" alt="Logo UMPKU Surakarta">
        </a>
        <div class="nav-menu">
            <a href="#home" class="nav-link active">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="#contact" class="nav-link">
                <i class="fas fa-envelope"></i>
                <span>Contact</span>
            </a>
        </div>
    </nav>

    <!-- Login Section -->
    <section id="home" class="page-section">
        <div class="login-wrapper">
            <div class="logo-side">
                <img src="{{ asset('img/logoutama.png') }}" alt="Logo UMPKU" class="main-logo">
                <p class="tagline">&bull; New Era, New Vibes &bull;</p>
            </div>

            <div class="login-container">
                <div class="login-header">
                    <h2>Login {{ $roleData['label'] }}</h2>
                    <p>UMPKU Surakarta - {{ $roleData['label'] }}</p>
                </div>

                {{-- Error Message --}}
                @if(session('error'))
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Google Login Button --}}
                <div class="google-auth-wrapper" style="margin-top: 16px;">
                    <a href="{{ route('auth.google') }}" class="btn-google">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Login dengan Google
                    </a>
                </div>

                {{-- Register Link --}}
                <a href="/register-hotspot/{{ $role }}" class="btn-register">
                    <i class="{{ $roleData['icon'] }}"></i> Buat Akun {{ $roleData['label'] }}
                </a>

                {{-- Back link --}}
                <div class="back-link">
                    <a href="/hotspot/login">
                        <i class="fas fa-arrow-left"></i> Pilih Jaringan Lain
                    </a>
                </div>

                <div class="footer-link">
                    <a href="#contact">Butuh bantuan?</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="page-section">
        <div class="contact-wrapper">
            <div class="contact-header">
                <h2>Hubungi Kami</h2>
                <p>Jangan ragu untuk menghubungi kami jika membutuhkan informasi</p>
            </div>

            <div class="contact-content">
                <div class="contact-map">
                    <img src="{{ asset('img/maps.png') }}" alt="Lokasi UMPKU Surakarta" style="width: 100%; height: 100%; object-fit: cover;">
                </div>

                <div class="contact-info">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <img src="{{ asset('img/iconmaps.png') }}" alt="Maps" style="width: 24px; height: 24px;">
                        </div>
                        <div class="contact-details">
                            <h4>Alamat Kantor</h4>
                            <p>Jl. Tulang Bawang Sel. No.26, Kadipiro,<br>Kec. Banjarsari, Kota Surakarta,<br>Jawa Tengah 57136</p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="contact-icon">
                            <img src="{{ asset('img/iconkontak.png') }}" alt="Kontak" style="width: 24px; height: 24px;">
                        </div>
                        <div class="contact-details">
                            <h4>Telepon</h4>
                            <p>(0271) 123456</p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="contact-icon">
                            <img src="{{ asset('img/iconemail.png') }}" alt="Email" style="width: 24px; height: 24px;">
                        </div>
                        <div class="contact-details">
                            <h4>Email</h4>
                            <p>info@umpkusurakarta.ac.id</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="social-section">
                <div class="social-links">
                    <a href="#" class="social-link">Facebook</a>
                    <a href="#" class="social-link">Twitter</a>
                    <a href="#" class="social-link">Google</a>
                    <a href="#" class="social-link">WhatsApp</a>
                    <a href="#" class="social-link">Instagram</a>
                    <a href="#" class="social-link">YouTube</a>
                </div>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <p>&copy; {{ date('Y') }} UMPKU Surakarta. Powered by Tim IT UMPKU</p>
    </footer>

    <script>
        // Wallpaper slideshow
        (function() {
            var slides = document.querySelectorAll('.wallpaper-bg .slide');
            if (slides.length < 2) return;
            var current = 0;
            setInterval(function() {
                slides[current].style.opacity = '0';
                current = (current + 1) % slides.length;
                slides[current].style.opacity = '1';
            }, 6000);
        })();

        // Active nav link on scroll
        var navLinks = document.querySelectorAll('.nav-link');
        var sections = document.querySelectorAll('.page-section');
        window.addEventListener('scroll', function() {
            var current = '';
            sections.forEach(function(section) {
                var sectionTop = section.offsetTop;
                if (scrollY >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });
            navLinks.forEach(function(link) {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>

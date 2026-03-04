<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Services\MikrotikService;
use App\Services\MikrotikCacheService;
use App\Helpers\LogActivityHelper;

class HotspotController extends Controller
{
    /**
     * Map nama hotspot server MikroTik ke role user.
     * Sesuaikan key dengan nama server di /ip hotspot.
     */
    protected array $serverRoleMap = [
        'hs-dosen'     => 'dosen',
        'hs-mahasiswa' => 'mahasiswa',
        'hs-staff'     => 'staff',
        'hs-tamu'      => 'tamu',
        'hotspot-dosen'     => 'dosen',
        'hotspot-mahasiswa' => 'mahasiswa',
        'hotspot-staff'     => 'staff',
        'hotspot-tamu'      => 'tamu',
        'dosen'        => 'dosen',
        'mahasiswa'    => 'mahasiswa',
        'staff'        => 'staff',
        'tamu'         => 'tamu',
    ];

    /**
     * Info visual per role.
     */
    protected array $roleInfo = [
        'dosen'     => ['label' => 'Dosen',     'icon' => 'fas fa-chalkboard-teacher', 'color' => 'blue'],
        'mahasiswa' => ['label' => 'Mahasiswa', 'icon' => 'fas fa-user-graduate',       'color' => 'emerald'],
        'staff'     => ['label' => 'Staff',     'icon' => 'fas fa-id-badge',            'color' => 'purple'],
        'tamu'      => ['label' => 'Tamu',      'icon' => 'fas fa-user-tag',            'color' => 'amber'],
    ];

    /**
     * Tampilkan halaman login hotspot (captive portal) — halaman utama pemilih role.
     * MikroTik mengirim params: mac, ip, link-login-only, link-orig, dst, server.
     */
    public function showLogin(Request $request)
    {
        // Simpan parameter captive portal MikroTik ke session
        if ($request->has('mac') || $request->has('ip')) {
            session([
                'hotspot_mac'        => $request->query('mac'),
                'hotspot_ip'         => $request->query('ip'),
                'hotspot_link_login' => $request->query('link-login-only', $request->query('link-login')),
                'hotspot_link_orig'  => $request->query('link-orig', $request->query('dst')),
            ]);
        }

        // Deteksi role dari parameter 'server' yang dikirim MikroTik
        $serverName = strtolower(trim($request->query('server', session('hotspot_server', ''))));
        if ($serverName) {
            session(['hotspot_server' => $serverName]);
        }

        $detectedRole = $this->serverRoleMap[$serverName] ?? null;

        // Jika role terdeteksi dari MikroTik, langsung redirect ke halaman login role tersebut
        if ($detectedRole) {
            return redirect("/hotspot/login/{$detectedRole}");
        }

        // Tampilkan halaman pemilih role
        return view('hotspot.login');
    }

    /**
     * Tampilkan halaman login per role (dosen/mahasiswa/staff/tamu).
     */
    public function showLoginRole(Request $request, string $role)
    {
        $roleData = $this->roleInfo[$role] ?? null;

        if (!$roleData) {
            return redirect('/hotspot/login');
        }

        // Simpan role ke session agar callback tahu harus redirect kemana
        session(['hotspot_login_role' => $role]);

        return view('hotspot.login-role', [
            'role'     => $role,
            'roleData' => $roleData,
        ]);
    }

    /**
     * Redirect ke Google OAuth (LOGIN flow).
     * Menggunakan stateless() agar tidak bergantung pada session saat redirect.
     */
    public function redirectToGoogle()
    {
        $role = session('hotspot_login_role', 'tamu');

        // Hapus register_profile agar callback tidak salah deteksi
        session()->forget('register_profile');

        // Encode SEMUA data yang dibutuhkan ke state parameter
        // Ini KRITIS: session bisa hilang saat redirect ke Google (domain berbeda, SameSite cookie, dll)
        // State parameter akan di-pass oleh Google kembali ke callback URL
        $state = base64_encode(json_encode([
            'intent'     => 'login',
            'role'       => $role,
            'link_login' => session('hotspot_link_login'),
            'link_orig'  => session('hotspot_link_orig'),
            'mac'        => session('hotspot_mac'),
            'ip'         => session('hotspot_ip'),
        ]));

        \Log::info('[Hotspot] Login redirectToGoogle', [
            'role'       => $role,
            'link_login' => session('hotspot_link_login'),
            'mac'        => session('hotspot_mac'),
            'ip'         => session('hotspot_ip'),
        ]);

        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $state])
            ->redirect();
    }

    /**
     * Handle callback dari Google OAuth.
     * Mendeteksi flow register atau login berdasarkan state parameter (primary) + session (fallback).
     */
    public function handleGoogleCallback(Request $request, MikrotikService $mikrotik)
    {
        $rawState = $request->query('state', '');

        \Log::info('[Hotspot] === OAuth CALLBACK HIT ===', [
            'url'             => $request->fullUrl(),
            'has_code'        => $request->has('code'),
            'has_error'       => $request->has('error'),
            'error_msg'       => $request->query('error'),
            'state_raw'       => $rawState,
            'session_profile' => session('register_profile'),
            'session_role'    => session('hotspot_login_role'),
        ]);

        // Jika Google mengembalikan error
        if ($request->has('error')) {
            \Log::error('[Hotspot] Google returned error', ['error' => $request->query('error')]);
            return redirect('/hotspot/login')->with('error', 'Google OAuth gagal: ' . $request->query('error'));
        }

        // Decode state parameter dari Google redirect (UTAMA, tidak bergantung session)
        $stateData = $this->decodeOAuthState($rawState);

        \Log::info('[Hotspot] State decoded', ['stateData' => $stateData]);

        // Tentukan intent: dari state (primary) atau session (fallback)
        $intent  = $stateData['intent'] ?? null;
        $profile = $stateData['profile'] ?? session('register_profile');
        $role    = $stateData['role'] ?? session('hotspot_login_role');

        // RESTORE hotspot params dari state ke session
        // State parameter survive Google OAuth redirect, session mungkin tidak
        if (!empty($stateData['link_login'])) {
            session(['hotspot_link_login' => $stateData['link_login']]);
        }
        if (!empty($stateData['link_orig'])) {
            session(['hotspot_link_orig' => $stateData['link_orig']]);
        }
        if (!empty($stateData['mac'])) {
            session(['hotspot_mac' => $stateData['mac']]);
        }
        if (!empty($stateData['ip'])) {
            session(['hotspot_ip' => $stateData['ip']]);
        }

        \Log::info('[Hotspot] Flow decision', [
            'intent'     => $intent,
            'profile'    => $profile,
            'role'       => $role,
            'link_login' => session('hotspot_link_login'),
            'mac'        => session('hotspot_mac'),
            'ip'         => session('hotspot_ip'),
        ]);

        // REGISTER FLOW: jika intent = register DAN profile terdeteksi
        if ($intent === 'register' && !empty($profile)) {
            session(['register_profile' => $profile]);
            \Log::info('[Hotspot] >>> Entering REGISTER flow', ['profile' => $profile]);
            return $this->handleGoogleRegister($mikrotik);
        }

        // FALLBACK: jika tidak ada intent tapi ada register_profile di session
        if (!$intent && session('register_profile')) {
            \Log::info('[Hotspot] >>> Entering REGISTER flow via session fallback', [
                'profile' => session('register_profile'),
            ]);
            return $this->handleGoogleRegister($mikrotik);
        }

        // LOGIN FLOW
        if ($role) {
            session(['hotspot_login_role' => $role]);
        }

        \Log::info('[Hotspot] >>> Entering LOGIN flow', ['role' => $role]);
        return $this->handleGoogleLogin($request, $mikrotik);
    }

    /**
     * Decode state parameter dari Google OAuth redirect.
     */
    private function decodeOAuthState(?string $state): array
    {
        if (!$state) {
            return [];
        }

        try {
            $decoded = json_decode(base64_decode($state), true);
            return is_array($decoded) ? $decoded : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle Google OAuth LOGIN flow.
     * Cocokkan email Google dengan user hotspot MikroTik yang sudah ada.
     * Lalu login ke MikroTik hotspot via link-login-only (auto-POST credentials).
     */
    private function handleGoogleLogin(Request $request, MikrotikService $mikrotik)
    {
        $email = 'unknown';

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $email = $googleUser->getEmail();
            $name = $googleUser->getName();
            $macAddress = session('hotspot_mac');
            $clientIp = session('hotspot_ip', $request->ip());

            // Jika MAC belum ada di session, coba ambil dari MikroTik host table
            if (!$macAddress && $clientIp) {
                try {
                    $host = $mikrotik->getHostByIp($clientIp);
                    $macAddress = $host['mac-address'] ?? null;
                } catch (\Exception $e) {
                    // Abaikan — MAC opsional
                }
            }

            // Cari user hotspot berdasarkan email
            $hotspotUser = $mikrotik->findHotspotUserByEmail($email);

            if (!$hotspotUser) {
                throw new \RuntimeException('Email tidak ditemukan di daftar user hotspot MikroTik.');
            }

            // Jika user disabled, aktifkan dulu
            if (($hotspotUser['disabled'] ?? 'false') === 'true') {
                $mikrotik->enableUser($hotspotUser['.id']);
            }

            $username = $hotspotUser['name'] ?? $email;

            // Set temporary password untuk login ke MikroTik hotspot
            $tempPassword = Str::random(12);
            $mikrotik->resetPassword($hotspotUser['.id'], $tempPassword);

            \Log::info('[Hotspot] Google login: temp password set for MikroTik hotspot login', [
                'username' => $username,
                'email'    => $email,
            ]);

            // Tetap buat IP-binding bypass sebagai fallback
            if ($macAddress) {
                try {
                    $mikrotik->removeGoogleOAuthBindings($macAddress);
                    $mikrotik->addIpBinding($macAddress, 'bypassed', 'google-oauth:' . $email);
                } catch (\Exception $e) {
                    // IP-binding gagal bukan masalah fatal — login via credentials tetap jalan
                }
            }

            // Log berhasil
            LogActivityHelper::logHotspot(
                'google_login',
                $email,
                $username,
                'success'
            );

            // Ambil link_login dari session (disimpan saat MikroTik redirect ke captive portal)
            $linkLogin = session('hotspot_link_login');
            $linkOrig  = session('hotspot_link_orig');

            session()->forget(['hotspot_mac', 'hotspot_ip', 'hotspot_link_login', 'hotspot_link_orig']);

            return redirect('/hotspot/success')->with([
                'login_success'  => true,
                'email'          => $email,
                'google_name'    => $name,
                'username'       => $username,
                'password'       => $tempPassword,
                'profile'        => $hotspotUser['profile'] ?? 'default',
                'link_login'     => $linkLogin,
                'link_orig'      => $linkOrig,
            ]);

        } catch (\Exception $e) {
            LogActivityHelper::logHotspot('google_login', $email, null, 'failed');

            // Redirect ke halaman login role yang sesuai dengan pesan error
            $role = session('hotspot_login_role');
            $serverName = strtolower(trim(session('hotspot_server', '')));
            $detectedRole = $role ?? ($this->serverRoleMap[$serverName] ?? null);

            if ($detectedRole) {
                return redirect("/hotspot/login/{$detectedRole}")
                    ->with('error', 'Login gagal: ' . $e->getMessage());
            }

            return redirect('/hotspot/login')->with('error', 'Login gagal: ' . $e->getMessage());
        }
    }

    /**
     * Handle Google OAuth REGISTER flow.
     * Membuat user hotspot baru di MikroTik menggunakan email Google.
     */
    private function handleGoogleRegister(MikrotikService $mikrotik)
    {
        $profile = session('register_profile', 'default');

        // Map profile ke role untuk redirect
        $roleMap = [
            '@dosen'          => 'dosen',
            '@mahasiswa'      => 'mahasiswa',
            '@staff'          => 'staff',
            'default'         => 'tamu',
            // Legacy names (backward compat)
            'DosenMagang'     => 'dosen',
            'MahasiswaMagang' => 'mahasiswa',
            'StaffMagang'     => 'staff',
            'TamuMagang'      => 'tamu',
        ];
        $role = $roleMap[$profile] ?? 'tamu';

        \Log::info('[Hotspot] Google Register flow started', [
            'profile' => $profile,
            'role'    => $role,
        ]);

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $email = $googleUser->getEmail();
            $name  = $googleUser->getName();

            \Log::info('[Hotspot] Google user retrieved for registration', [
                'email' => $email,
                'name'  => $name,
            ]);

            // Cek apakah email sudah terdaftar
            $existing = $mikrotik->findHotspotUserByEmail($email);

            if ($existing) {
                \Log::info('[Hotspot] Email sudah terdaftar, skip register', [
                    'email'    => $email,
                    'username' => $existing['name'],
                ]);
                session()->forget('register_profile');
                return redirect("/register-hotspot/{$role}")
                    ->with('error', "Email {$email} sudah terdaftar sebagai user hotspot (username: {$existing['name']}).");
            }

            // Generate username dari email & random password
            $username = Str::before($email, '@');
            $password = Str::random(8);

            \Log::info('[Hotspot] Attempting to create MikroTik hotspot user', [
                'username' => $username,
                'profile'  => $profile,
                'email'    => $email,
            ]);

            $result = $mikrotik->addHotspotUser([
                'name'     => $username,
                'password' => $password,
                'profile'  => $profile,
                'comment'  => 'email:' . $email,
            ]);

            \Log::info('[Hotspot] MikroTik addHotspotUser response', [
                'username' => $username,
                'result'   => $result,
            ]);

            // Invalidate cache agar user baru muncul di halaman manajemen
            try {
                app(MikrotikCacheService::class)->invalidateUserCaches();
                \Log::info('[Hotspot] Cache invalidated after register');
            } catch (\Exception $cacheEx) {
                \Log::warning('[Hotspot] Cache invalidation failed', ['error' => $cacheEx->getMessage()]);
            }

            // Log aktivitas
            LogActivityHelper::logHotspot(
                'google_register',
                $email,
                $username,
                'success'
            );

            session()->forget('register_profile');

            return redirect()->route('hotspot.registerSuccess')
                ->with('reg_name', $name)
                ->with('reg_email', $email)
                ->with('reg_username', $username)
                ->with('reg_password', $password)
                ->with('reg_profile', $profile);

        } catch (\Exception $e) {
            \Log::error('[Hotspot] Google Register FAILED', [
                'profile' => $profile,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            LogActivityHelper::logHotspot(
                'google_register',
                $email ?? 'unknown',
                null,
                'failed'
            );

            session()->forget('register_profile');
            return redirect("/register-hotspot/{$role}")
                ->with('error', 'Gagal registrasi via Google: ' . $e->getMessage());
        }
    }

    /**
     * Halaman sukses setelah login hotspot via Google.
     */
    public function success()
    {
        if (!session('login_success')) {
            return redirect('/hotspot/login');
        }

        return view('hotspot.success');
    }
}

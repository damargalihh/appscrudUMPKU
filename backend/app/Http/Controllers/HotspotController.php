<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Services\MikrotikService;
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
     * Tampilkan halaman login hotspot (captive portal).
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
        $roleData     = $detectedRole ? ($this->roleInfo[$detectedRole] ?? null) : null;

        return view('hotspot.login', [
            'detectedRole' => $detectedRole,
            'roleData'     => $roleData,
            'serverName'   => $serverName,
        ]);
    }

    /**
     * Redirect ke Google OAuth.
     */
    public function redirectToGoogle()
    {
        session()->forget('register_profile');

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google OAuth.
     * Mendeteksi flow register atau login berdasarkan session.
     */
    public function handleGoogleCallback(Request $request, MikrotikService $mikrotik)
    {
        // Cek apakah ini flow registrasi (dari SelfRegisterController)
        if (session('register_profile')) {
            return $this->handleGoogleRegister($mikrotik);
        }

        return $this->handleGoogleLogin($request, $mikrotik);
    }

    /**
     * Handle Google OAuth LOGIN flow.
     * Cocokkan email Google dengan user hotspot MikroTik yang sudah ada.
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

            // Cocokkan email dengan user hotspot MikroTik
            $hotspotUser = $mikrotik->connectUser($email, $macAddress);

            // Log berhasil
            LogActivityHelper::logHotspot(
                'google_login',
                $email,
                $hotspotUser['name'] ?? $email,
                'success'
            );

            // Bersihkan session captive portal
            $linkOrig = session('hotspot_link_orig');
            session()->forget(['hotspot_mac', 'hotspot_ip', 'hotspot_link_login', 'hotspot_link_orig']);

            return redirect('/hotspot/success')->with([
                'login_success' => true,
                'email'         => $email,
                'google_name'   => $name,
                'username'      => $hotspotUser['name'] ?? $email,
                'profile'       => $hotspotUser['profile'] ?? 'default',
                'link_orig'     => $linkOrig,
            ]);

        } catch (\Exception $e) {
            LogActivityHelper::logHotspot('google_login', $email, null, 'failed');

            return redirect('/hotspot/login')->with('error', 'Login gagal: ' . $e->getMessage());
        }
    }

    /**
     * Handle Google OAuth REGISTER flow.
     * Membuat user hotspot baru di MikroTik menggunakan email Google.
     */
    private function handleGoogleRegister(MikrotikService $mikrotik)
    {
        $profile = session('register_profile', 'TamuMagang');

        // Map profile ke role untuk redirect
        $roleMap = [
            'DosenMagang'     => 'dosen',
            'MahasiswaMagang' => 'mahasiswa',
            'StaffMagang'     => 'staff',
            'TamuMagang'      => 'tamu',
        ];
        $role = $roleMap[$profile] ?? 'tamu';

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $email = $googleUser->getEmail();
            $name  = $googleUser->getName();

            // Cek apakah email sudah terdaftar
            $existing = $mikrotik->findHotspotUserByEmail($email);

            if ($existing) {
                session()->forget('register_profile');
                return redirect("/register-hotspot/{$role}")
                    ->with('error', "Email {$email} sudah terdaftar sebagai user hotspot (username: {$existing['name']}).");
            }

            // Generate username dari email & random password
            $username = Str::before($email, '@');
            $password = Str::random(8);

            $mikrotik->addHotspotUser([
                'name'     => $username,
                'password' => $password,
                'profile'  => $profile,
                'comment'  => 'email:' . $email,
            ]);

            session()->forget('register_profile');

            return redirect()->route('hotspot.registerSuccess')
                ->with('reg_name', $name)
                ->with('reg_email', $email)
                ->with('reg_username', $username)
                ->with('reg_password', $password)
                ->with('reg_profile', $profile);

        } catch (\Exception $e) {
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

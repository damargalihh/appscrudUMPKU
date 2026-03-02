<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Services\MikrotikService;
use App\Helpers\LogActivityHelper;

class HotspotController extends Controller
{
    /**
     * Tampilkan halaman login hotspot (captive portal).
     * MikroTik mengirim params: mac, ip, link-login-only, link-orig, dst.
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

        return view('hotspot.login');
    }

    /**
     * Redirect ke Google OAuth.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google OAuth.
     * Cocokkan email Google dengan user hotspot MikroTik.
     */
    public function handleGoogleCallback(Request $request, MikrotikService $mikrotik)
    {
        $googleUser = null;
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
            // Log gagal
            LogActivityHelper::logHotspot('google_login', $email, null, 'failed');

            return redirect('/hotspot/login')->with('error', 'Login gagal: ' . $e->getMessage());
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

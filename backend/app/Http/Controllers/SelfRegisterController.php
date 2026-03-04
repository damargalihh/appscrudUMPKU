<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelfRegisterRequest;
use App\Services\MikrotikService;
use App\Services\MikrotikCacheService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SelfRegisterController extends Controller
{
    /**
     * Tampilkan form registrasi berdasarkan role (dosen, mahasiswa, staff, tamu).
     */
    public function showForm(Request $request)
    {
        $role = $request->route()->defaults['role'] ?? 'tamu';
        $view = "hotspot.register-{$role}";

        if (!view()->exists($view)) {
            abort(404);
        }

        return view($view);
    }

    /**
     * Proses self-registration hotspot user (form manual).
     */
    public function selfRegister(SelfRegisterRequest $request, MikrotikService $mt)
    {
        try {
            $data = $request->validated();

            // Simpan email di comment field MikroTik (untuk pencocokan Google OAuth login)
            if (!empty($data['email'])) {
                $data['comment'] = 'email:' . $data['email'];
            }

            $mt->addHotspotUser($data);

            // Invalidate cache agar user baru muncul di halaman manajemen
            try {
                app(MikrotikCacheService::class)->invalidateUserCaches();
            } catch (\Exception $cacheEx) {
                // Cache invalidation gagal bukan masalah fatal
            }

            return back()->with('success', 'Registrasi berhasil! Username: ' . $data['name']);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Redirect ke Google OAuth untuk registrasi hotspot.
     * Menggunakan stateless() agar tidak bergantung pada session saat redirect.
     * Intent dan profile di-encode ke state parameter OAuth agar
     * HotspotController callback bisa mendeteksi flow register.
     */
    public function redirectToGoogle(Request $request)
    {
        $profile = $request->query('profile', 'default');

        // Simpan ke session sebagai fallback
        session(['register_profile' => $profile]);

        // Encode register intent ke state parameter OAuth (primary method)
        $state = base64_encode(json_encode([
            'intent'  => 'register',
            'profile' => $profile,
        ]));

        \Log::info('[Hotspot] Register redirectToGoogle', [
            'profile' => $profile,
            'state'   => $state,
        ]);

        // stateless() agar Socialite tidak mencoba menulis session (mencegah error jika DB session bermasalah)
        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $state])
            ->redirect();
    }

    /**
     * Halaman sukses setelah registrasi via Google.
     */
    public function registerSuccess()
    {
        if (!session('reg_username')) {
            return redirect('/hotspot/login');
        }

        return view('hotspot.register-success');
    }
}

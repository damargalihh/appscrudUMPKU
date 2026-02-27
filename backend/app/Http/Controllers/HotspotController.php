<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Services\MikrotikService;

class HotspotController extends Controller
{
    // Show login page
    public function login()
    {
        return view('auth.login');
    }

    // Redirect to Google
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle Google callback
    public function callback(Request $request, MikrotikService $mikrotik)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->email)->first();
            if ($user) {
                Auth::login($user);
                // Koneksi internet: panggil MikrotikService jika perlu
                $mikrotik->connectUser($user);
                return redirect()->intended('/dashboard')->with('success', 'Login berhasil, koneksi internet aktif.');
            } else {
                return redirect('/login')->with('error', 'Email tidak terdaftar.');
            }
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Login gagal: ' . $e->getMessage());
        }
    }
}

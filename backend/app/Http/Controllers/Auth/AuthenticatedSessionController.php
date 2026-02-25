<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $status = 'success';
        $user = null;
        try {
            $request->authenticate();
            $user = Auth::user();
            $request->session()->regenerate();
        } catch (\Throwable $e) {
            $status = 'failed';
        }
        \App\Helpers\LogActivityHelper::log('login', $user ? $user->email : null, $status, $user);
        if ($status === 'failed') {
            return back()->withErrors(['email' => 'Login gagal.'])->withInput();
        }
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        \App\Helpers\LogActivityHelper::log('logout', $user ? $user->email : null, 'success', $user);
        return redirect('/');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelfRegisterRequest;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

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
     * Proses self-registration hotspot user.
     */
    public function selfRegister(SelfRegisterRequest $request, MikrotikService $mt)
    {
        try {
            $mt->addHotspotUser($request->validated());
            return back()->with('success', 'Registrasi berhasil');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

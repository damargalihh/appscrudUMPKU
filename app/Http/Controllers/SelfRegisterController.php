<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikService;

class SelfRegisterController extends Controller
{
    // public function showRegister()
    // {
    //     return view('hotspot.register');
    // }

    public function showRegisterDosen()
    {
        return view('hotspot.register-dosen');
    }

    public function showRegisterMahasiswa()
    {
        return view('hotspot.register-mahasiswa');
    }

    public function showRegisterStaff()
    {
        return view('hotspot.register-staff');
    }

    public function showRegisterTamu()
    {
        return view('hotspot.register-tamu');
    }
    
    public function selfRegister(Request $request, MikrotikService $mt)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required|min:4',
        ]);
    
        try {
            $mt->addHotspotUser([
                'name' => $request->name,
                'password' => $request->password,
                'profile' => $request->profile,
            ]);
    
            return back()->with('success', 'Registrasi berhasil');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

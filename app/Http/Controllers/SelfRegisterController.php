<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikService;

class SelfRegisterController extends Controller
{
    public function showRegister()
    {
        return view('hotspot.register');
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
    
            return redirect()
                ->route('hotspot.selfRegister')
                ->with('success', 'Registrasi berhasil');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

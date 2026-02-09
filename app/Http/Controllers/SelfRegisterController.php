<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikService;

class SelfRegisterController extends Controller
{
    public function showRegister(\App\Services\MikrotikService $mt)
    {
        try {
            $profiles = $mt->getProfiles();
        } catch (\Exception $e) {
            $profiles = [];
        }
    
        return view('hotspot.register', compact('profiles'));
    }
    
    public function selfRegister(Request $request, MikrotikService $mt)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required|min:4',
            'profile' => 'required'
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

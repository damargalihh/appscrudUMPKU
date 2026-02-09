<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikService;

class HotspotUserController extends Controller
{
    public function dashboard(\App\Services\MikrotikService $mt)
    {
        try {
            $users       = $mt->getHotspotUsers();
            $activeUsers = $mt->getActiveUsers();
            $profiles    = $mt->getProfiles();
        } catch (\Exception $e) {
            $users = $activeUsers = $profiles = [];
        }

        return view('dashboard', compact('users', 'activeUsers', 'profiles'));
    }
    
    /**
     * Tampilkan semua user hotspot
     */
    public function index(MikrotikService $mt)
    {
        try {
            $users    = $mt->getHotspotUsers();
            $profiles = $mt->getProfiles();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengambil data MikroTik: ' . $e->getMessage());
        }

        return view('hotspot.index', compact('users', 'profiles'));
    }

    /**
     * Tambah user hotspot
     */
    public function store(Request $request, MikrotikService $mt)
    {
        $request->validate([
            'name'     => 'required|string',
            'password' => 'required|string|min:4',
            'profile'  => 'nullable|string',
        ]);

        try {
            $mt->addHotspotUser([
                'name'     => $request->name,
                'password' => $request->password,
                'profile'  => $request->profile,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambah user hotspot: ' . $e->getMessage());
        }

        return back()->with('success', 'User hotspot berhasil ditambahkan');
    }

    /**
     * Hapus user hotspot
     */
    public function destroy(string $id, MikrotikService $mt)
    {
        try {
            $mt->deleteHotspotUser($id);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user hotspot: ' . $e->getMessage());
        }

        return back()->with('success', 'User hotspot berhasil dihapus');
    }

    /**
     * Reset password user hotspot
     */
    public function resetPassword(Request $request, string $id, MikrotikService $mt)
    {
        $request->validate([
            'password' => 'required|min:4'
        ]);

        try {
            $mt->resetPassword($id, $request->password);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal reset password: ' . $e->getMessage());
        }

        return back()->with('success', 'Password berhasil direset');
    }

    /**
     * Disable user hotspot
     */
    public function disable(string $id, MikrotikService $mt)
    {
        try {
            $mt->disableUser($id);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal disable user: ' . $e->getMessage());
        }

        return back()->with('success', 'User berhasil di-disable');
    }

    /**
     * Enable user hotspot
     */
    public function enable(string $id, MikrotikService $mt)
    {
        try {
            $mt->enableUser($id);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal enable user: ' . $e->getMessage());
        }

        return back()->with('success', 'User berhasil di-enable');
    }

    /**
     * User hotspot aktif
     */
    public function active(MikrotikService $mt)
    {
        try {
            $users = $mt->getActiveUsers();
        } catch (\Exception $e) {
            $users = [];
        }

        return view('hotspot.active', compact('users'));
    }

    /**
     * Hapus profile hotspot
     */
    public function destroyProfile(string $id, MikrotikService $mt)
    {
        try {
            $mt->deleteProfile($id);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus profile: ' . $e->getMessage());
        }

        return back()->with('success', 'Profile berhasil dihapus');
    }

    /**
     * API: Bandwidth data realtime (JSON)
     */
    public function apiBandwidth(MikrotikService $mt)
    {
        try {
            $queues = $mt->getQueues();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $data = collect($queues)->map(function ($q) {
            $rate = $q['rate'] ?? '0/0';
            $rates = explode('/', $rate);
            $upload = intval($rates[0] ?? 0);
            $download = intval($rates[1] ?? 0);

            $maxLimit = $q['max-limit'] ?? '0/0';
            $maxParts = explode('/', $maxLimit);
            $maxUp = intval($maxParts[0] ?? 0);
            $maxDown = intval($maxParts[1] ?? 0);

            return [
                'name'        => $q['name'] ?? ($q['target'] ?? '-'),
                'target'      => $q['target'] ?? '-',
                'upload'      => $upload,
                'download'    => $download,
                'maxUp'       => $maxUp,
                'maxDown'     => $maxDown,
                'upPercent'   => $maxUp > 0 ? min(round(($upload / $maxUp) * 100), 100) : 0,
                'downPercent' => $maxDown > 0 ? min(round(($download / $maxDown) * 100), 100) : 0,
            ];
        });

        return response()->json($data);
    }
}

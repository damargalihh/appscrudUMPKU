<?php

namespace App\Helpers;

use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

class LogActivityHelper
{
    /**
     * Log aktivitas admin (user harus terautentikasi di sistem).
     *
     * @param string $action
     * @param string|null $targetUsername
     * @param string $status
     * @param \App\Models\User|null $user
     */
    public static function log($action, $targetUsername = null, $status = 'success', $user = null)
    {
        $user = $user ?: Auth::user();
        if (!$user) return;

        $aksi = $action;
        if ($targetUsername) {
            $aksi .= ' - ' . $targetUsername;
        }

        LogActivity::create([
            'user_id'    => $user->id,
            'username'   => $user->email,
            'role'       => $user->role ?? 'admin',
            'action'     => $aksi,
            'ip_address' => request()->ip(),
            'status'     => $status,
        ]);
    }

    /**
     * Log aktivitas hotspot (user mungkin tidak ada di tabel users).
     * Digunakan untuk Google OAuth login hotspot.
     *
     * @param string $action
     * @param string $email
     * @param string|null $targetUsername
     * @param string $status
     */
    public static function logHotspot($action, $email, $targetUsername = null, $status = 'success')
    {
        $aksi = $action;
        if ($targetUsername) {
            $aksi .= ' - ' . $targetUsername;
        }

        LogActivity::create([
            'user_id'    => null,
            'username'   => $email ?? 'unknown',
            'role'       => 'hotspot_user',
            'action'     => $aksi,
            'ip_address' => request()->ip(),
            'status'     => $status,
        ]);
    }
}

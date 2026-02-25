<?php

namespace App\Helpers;

use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

class LogActivityHelper
{
    /**
     * Log aktivitas dengan format aksi - username/email/ID jika ada target.
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
            'role'       => $user->role,
            'action'     => $aksi,
            'ip_address' => request()->ip(),
            'status'     => $status,
        ]);
    }
}

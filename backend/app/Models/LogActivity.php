<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'role',
        'action',
        'ip_address',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label role yang lebih ramah pengguna.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin'  => 'Full Admin',
            'admin'        => 'Admin',
            'hotspot_user' => 'Hotspot User',
            default        => $this->role ?? 'Unknown',
        };
    }

    /**
     * Apakah log ini dari hotspot user (bukan admin).
     */
    public function isHotspotUser(): bool
    {
        return $this->role === 'hotspot_user';
    }
}

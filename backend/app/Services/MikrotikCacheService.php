<?php

namespace App\Services;

use App\Helpers\FormatHelper;
use App\Models\MikrotikCache;

/**
 * Service yang menangani caching data MikroTik ke database.
 *
 * Alur:
 * 1. Coba fetch dari MikroTik API
 * 2. Jika berhasil DAN data berubah → simpan ke DB, return data baru
 * 3. Jika gagal → return data terakhir dari DB (fallback)
 *
 * Ini mengurangi beban server karena:
 * - Data hanya di-write ke DB kalau ada perubahan
 * - Kalau MikroTik tidak bisa diakses (IP publik, timeout), tetap ada data
 */
class MikrotikCacheService
{
    protected MikrotikService $mt;

    public function __construct(MikrotikService $mt)
    {
        $this->mt = $mt;
    }

    // ─── HOTSPOT USERS ──────────────────────────────────────────

    public function getHotspotUsers(): array
    {
        return $this->fetchAndCache('hotspot_users', function () {
            $users = $this->mt->getHotspotUsers();

            return collect($users)->map(fn($u) => [
                'id'       => $u['.id'] ?? '',
                'name'     => $u['name'] ?? '-',
                'profile'  => $u['profile'] ?? '-',
                'disabled' => ($u['disabled'] ?? 'false') === 'true',
            ])->values()->all();
        });
    }

    // ─── PROFILES ───────────────────────────────────────────────

    public function getProfiles(): array
    {
        return $this->fetchAndCache('profiles', function () {
            $profiles = $this->mt->getProfiles();

            return collect($profiles)->map(fn($p) => [
                'id'        => $p['.id'] ?? '',
                'name'      => $p['name'] ?? '-',
                'rateLimit' => $p['rate-limit'] ?? 'Unlimited',
            ])->all();
        });
    }

    // ─── ACTIVE USERS ───────────────────────────────────────────

    public function getActiveUsers(): array
    {
        return $this->fetchAndCache('active_users', function () {
            $actives = $this->mt->getActiveUsers();

            return collect($actives)
                ->filter(fn($a) => (intval($a['bytes-in'] ?? 0) + intval($a['bytes-out'] ?? 0)) > 0)
                ->values()
                ->map(fn($a) => [
                    'id'       => $a['.id'] ?? '',
                    'user'     => $a['user'] ?? '-',
                    'address'  => $a['address'] ?? '-',
                    'uptime'   => $a['uptime'] ?? '-',
                    'rx'       => FormatHelper::bytes(intval($a['bytes-in'] ?? 0)),
                    'tx'       => FormatHelper::bytes(intval($a['bytes-out'] ?? 0)),
                    'rx_bytes' => intval($a['bytes-in'] ?? 0),
                    'tx_bytes' => intval($a['bytes-out'] ?? 0),
                ])->all();
        });
    }

    // ─── USER STATS ─────────────────────────────────────────────

    public function getUserStats(): array
    {
        return $this->fetchAndCache('user_stats', function () {
            $users       = $this->mt->getHotspotUsers();
            $activeUsers = $this->mt->getActiveUsers();

            $total    = count($users);
            $active   = count($activeUsers);
            $disabled = collect($users)->where('disabled', 'true')->count();
            $enabled  = $total - $disabled;

            return [
                'total'    => $total,
                'online'   => $active,
                'enabled'  => $enabled,
                'disabled' => $disabled,
                'time'     => now()->format('H:i:s'),
            ];
        });
    }

    // ─── SYSTEM INFO ────────────────────────────────────────────

    public function getSystemInfo(): array
    {
        return $this->fetchAndCache('system_info', function () {
            $resource = $this->mt->getSystemResource();
            $identity = $this->mt->getIdentity();

            $totalMem = intval($resource['total-memory'] ?? 0);
            $freeMem  = intval($resource['free-memory'] ?? 0);
            $usedMem  = $totalMem - $freeMem;
            $memPct   = $totalMem > 0 ? round(($usedMem / $totalMem) * 100) : 0;

            $totalHdd = intval($resource['total-hdd-space'] ?? 0);
            $freeHdd  = intval($resource['free-hdd-space'] ?? 0);
            $usedHdd  = $totalHdd - $freeHdd;
            $hddPct   = $totalHdd > 0 ? round(($usedHdd / $totalHdd) * 100) : 0;

            return [
                'identity'     => $identity,
                'board'        => $resource['board-name'] ?? '-',
                'version'      => $resource['version'] ?? '-',
                'uptime'       => $resource['uptime'] ?? '-',
                'cpu'          => $resource['cpu'] ?? '-',
                'cpuLoad'      => intval($resource['cpu-load'] ?? 0),
                'cpuCount'     => $resource['cpu-count'] ?? '1',
                'architecture' => $resource['architecture-name'] ?? '-',
                'totalMemory'  => FormatHelper::bytes($totalMem),
                'usedMemory'   => FormatHelper::bytes($usedMem),
                'freeMemory'   => FormatHelper::bytes($freeMem),
                'memPercent'   => $memPct,
                'totalHdd'     => FormatHelper::bytes($totalHdd),
                'usedHdd'      => FormatHelper::bytes($usedHdd),
                'freeHdd'      => FormatHelper::bytes($freeHdd),
                'hddPercent'   => $hddPct,
            ];
        });
    }

    // ─── BANDWIDTH / QUEUES ─────────────────────────────────────

    public function getBandwidth(): array
    {
        return $this->fetchAndCache('queues', function () {
            $queues = $this->mt->getQueues();

            return collect($queues)->map(function ($q) {
                $rate    = $q['rate'] ?? '0/0';
                $rates   = explode('/', $rate);
                $upload  = intval($rates[0] ?? 0);
                $download = intval($rates[1] ?? 0);

                $maxLimit = $q['max-limit'] ?? '0/0';
                $maxParts = explode('/', $maxLimit);
                $maxUp    = intval($maxParts[0] ?? 0);
                $maxDown  = intval($maxParts[1] ?? 0);

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
            })->all();
        });
    }

    // ─── CORE: FETCH + CACHE LOGIC ──────────────────────────────

    /**
     * Coba fetch data dari MikroTik, simpan ke DB kalau berubah.
     * Kalau gagal, return data cache terakhir dari DB.
     *
     * @param  string   $key      Cache key
     * @param  callable $fetcher  Closure yang fetch + transform data dari MikroTik
     * @return array              ['data' => [...], 'cached' => bool, 'fetched_at' => string]
     */
    protected function fetchAndCache(string $key, callable $fetcher): array
    {
        try {
            $freshData = $fetcher();

            // Bandingkan dengan cache di DB
            $existing = MikrotikCache::where('key', $key)->first();
            $existingData = $existing ? json_decode($existing->data, true) : null;

            if ($existingData === null || $this->dataChanged($existingData, $freshData)) {
                // Data berubah → simpan ke DB
                MikrotikCache::store($key, $freshData);
            }

            return [
                'data'       => $freshData,
                'cached'     => false,
                'fetched_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            // MikroTik gagal → coba ambil dari cache DB
            $cached = MikrotikCache::getCached($key);

            if ($cached) {
                return [
                    'data'       => $cached['data'],
                    'cached'     => true,
                    'fetched_at' => $cached['fetched_at']->toIso8601String(),
                ];
            }

            // Tidak ada cache → return default kosong agar halaman tetap tampil
            return [
                'data'       => $this->getDefaultData($key),
                'cached'     => true,
                'fetched_at' => now()->toIso8601String(),
                'empty'      => true,
            ];
        }
    }

    /**
     * Default empty data per key, agar dashboard/halaman tetap tampil
     * meskipun MikroTik belum pernah berhasil dihubungi.
     */
    protected function getDefaultData(string $key): array
    {
        return match ($key) {
            'user_stats' => [
                'total'    => 0,
                'online'   => 0,
                'enabled'  => 0,
                'disabled' => 0,
                'time'     => now()->format('H:i:s'),
            ],
            'system_info' => [
                'identity'     => '-',
                'board'        => '-',
                'version'      => '-',
                'uptime'       => '-',
                'cpu'          => '-',
                'cpuLoad'      => 0,
                'cpuCount'     => '0',
                'architecture' => '-',
                'totalMemory'  => '0 B',
                'usedMemory'   => '0 B',
                'freeMemory'   => '0 B',
                'memPercent'   => 0,
                'totalHdd'     => '0 B',
                'usedHdd'      => '0 B',
                'freeHdd'      => '0 B',
                'hddPercent'   => 0,
            ],
            default => [], // hotspot_users, profiles, active_users, queues → array kosong
        };
    }

    /**
     * Compare two data arrays to detect changes
     */
    protected function dataChanged(mixed $old, mixed $new): bool
    {
        return json_encode($old) !== json_encode($new);
    }

    /**
     * Invalidate (clear) cache for a specific key.
     * Dipanggil setelah melakukan write operation (add/delete/update user).
     */
    public function invalidate(string ...$keys): void
    {
        MikrotikCache::whereIn('key', $keys)->delete();
    }

    /**
     * Invalidate all hotspot-related caches.
     * Dipanggil setelah operasi CRUD user.
     */
    public function invalidateUserCaches(): void
    {
        $this->invalidate('hotspot_users', 'user_stats', 'active_users');
    }
}

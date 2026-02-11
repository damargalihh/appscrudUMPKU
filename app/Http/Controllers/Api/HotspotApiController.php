<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FormatHelper;
use App\Http\Controllers\Controller;
use App\Services\MikrotikService;

class HotspotApiController extends Controller
{
    /**
     * Profiles (JSON)
     */
    public function profiles(MikrotikService $mt)
    {
        try {
            $profiles = $mt->getProfiles();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(collect($profiles)->map(fn($p) => [
            'id'        => $p['.id'] ?? '',
            'name'      => $p['name'] ?? '-',
            'rateLimit' => $p['rate-limit'] ?? 'Unlimited',
        ]));
    }

    /**
     * Active users (JSON)
     */
    public function activeUsers(MikrotikService $mt)
    {
        try {
            $actives = $mt->getActiveUsers();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(collect($actives)
            ->filter(fn($a) => (intval($a['bytes-in'] ?? 0) + intval($a['bytes-out'] ?? 0)) > 0)
            ->values()
            ->map(fn($a) => [
                'user'    => $a['user'] ?? '-',
                'address' => $a['address'] ?? '-',
                'uptime'  => $a['uptime'] ?? '-',
                'rx'      => FormatHelper::bytes(intval($a['bytes-in'] ?? 0)),
                'tx'      => FormatHelper::bytes(intval($a['bytes-out'] ?? 0)),
            ]));
    }

    /**
     * Hotspot users (JSON)
     */
    public function hotspotUsers(MikrotikService $mt)
    {
        try {
            $users = $mt->getHotspotUsers();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(collect($users)->map(fn($u) => [
            'id'       => $u['.id'] ?? '',
            'name'     => $u['name'] ?? '-',
            'profile'  => $u['profile'] ?? '-',
            'disabled' => ($u['disabled'] ?? 'false') === 'true',
        ])->values());
    }

    /**
     * System info realtime (JSON)
     */
    public function systemInfo(MikrotikService $mt)
    {
        try {
            $resource = $mt->getSystemResource();
            $identity = $mt->getIdentity();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $totalMem = intval($resource['total-memory'] ?? 0);
        $freeMem  = intval($resource['free-memory'] ?? 0);
        $usedMem  = $totalMem - $freeMem;
        $memPct   = $totalMem > 0 ? round(($usedMem / $totalMem) * 100) : 0;

        $totalHdd = intval($resource['total-hdd-space'] ?? 0);
        $freeHdd  = intval($resource['free-hdd-space'] ?? 0);
        $usedHdd  = $totalHdd - $freeHdd;
        $hddPct   = $totalHdd > 0 ? round(($usedHdd / $totalHdd) * 100) : 0;

        return response()->json([
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
        ]);
    }

    /**
     * User stats realtime (JSON) for chart
     */
    public function userStats(MikrotikService $mt)
    {
        try {
            $users       = $mt->getHotspotUsers();
            $activeUsers = $mt->getActiveUsers();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $total    = count($users);
        $active   = count($activeUsers);
        $disabled = collect($users)->where('disabled', 'true')->count();
        $enabled  = $total - $disabled;

        return response()->json([
            'total'    => $total,
            'online'   => $active,
            'enabled'  => $enabled,
            'disabled' => $disabled,
            'time'     => now()->format('H:i:s'),
        ]);
    }

    /**
     * Bandwidth data realtime (JSON)
     */
    public function bandwidth(MikrotikService $mt)
    {
        try {
            $queues = $mt->getQueues();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $data = collect($queues)->map(function ($q) {
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
        });

        return response()->json($data);
    }
}

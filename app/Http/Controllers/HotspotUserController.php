<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class HotspotUserController extends Controller
{
    public function dashboard()
    {
        return view('dashboard');
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
     * Upload user hotspot dari file XLSX
     */
    public function uploadXlsx(Request $request, MikrotikService $mt)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file XLSX: ' . $e->getMessage());
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return back()->with('error', 'File XLSX kosong atau tidak memiliki data.');
        }

        $header = array_shift($rows);
        $headerMap = [];
        foreach ($header as $col => $name) {
            $key = strtolower(trim((string) $name));
            if ($key !== '') {
                $headerMap[$key] = $col;
            }
        }

        $requiredColumns = ['username', 'email', 'password', 'profile'];
        $missing = array_diff($requiredColumns, array_keys($headerMap));
        if (!empty($missing)) {
            return back()->with('error', 'Kolom wajib tidak lengkap: ' . implode(', ', $missing));
        }

        $successCount = 0;
        $errors = [];
        $rowNumber = 1;

        foreach ($rows as $row) {
            $rowNumber++;

            $username = trim((string) ($row[$headerMap['username']] ?? ''));
            $email = trim((string) ($row[$headerMap['email']] ?? ''));
            $password = trim((string) ($row[$headerMap['password']] ?? ''));
            $profile = trim((string) ($row[$headerMap['profile']] ?? ''));

            if ($username === '' && $email === '' && $password === '' && $profile === '') {
                continue;
            }

            $rowIssues = [];
            if ($username === '') {
                $rowIssues[] = 'username kosong';
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowIssues[] = 'email tidak valid';
            }
            if ($password === '') {
                $rowIssues[] = 'password kosong';
            }
            if ($profile === '') {
                $rowIssues[] = 'profile kosong';
            }

            if (!empty($rowIssues)) {
                $errors[] = 'Baris ' . $rowNumber . ': ' . implode(', ', $rowIssues);
                continue;
            }

            try {
                $mt->addHotspotUser([
                    'name' => $username,
                    'password' => $password,
                    'profile' => $profile,
                    'comment' => $email,
                ]);
                $successCount++;
            } catch (\Throwable $e) {
                $errors[] = 'Baris ' . $rowNumber . ': ' . $e->getMessage();
            }
        }

        $successMessage = 'Upload selesai. Berhasil: ' . $successCount . ' user.';

        if (!empty($errors)) {
            $preview = array_slice($errors, 0, 5);
            $suffix = count($errors) > 5 ? ' ...' : '';
            $errorMessage = 'Gagal: ' . count($errors) . ' baris. ' . implode(' | ', $preview) . $suffix;

            return back()->with('success', $successMessage)->with('error', $errorMessage);
        }

        return back()->with('success', $successMessage);
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
     * Hapus banyak user hotspot sekaligus
     */
    public function bulkDestroy(Request $request, MikrotikService $mt)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string',
        ]);

        $success = 0;
        $failed = 0;

        foreach ($request->ids as $id) {
            try {
                $mt->deleteHotspotUser($id);
                $success++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        $msg = "{$success} user berhasil dihapus";
        if ($failed > 0) {
            $msg .= ", {$failed} gagal dihapus";
        }

        return back()->with('success', $msg);
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
     * Disable user hotspot + kick dari jaringan
     */
    public function disable(string $id, MikrotikService $mt)
    {
        try {
            // Ambil username dulu sebelum disable
            $username = $mt->getUsernameById($id);

            // Disable user
            $mt->disableUser($id);

            // Kick dari active session agar langsung terputus
            if ($username) {
                $mt->kickActiveUser($username);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal disable user: ' . $e->getMessage());
        }

        return back()->with('success', 'User berhasil di-disable & terputus dari jaringan');
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
     * API: Profiles (JSON)
     */
    public function apiProfiles(MikrotikService $mt)
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
     * API: Active users (JSON)
     */
    public function apiActiveUsers(MikrotikService $mt)
    {
        try {
            $actives = $mt->getActiveUsers();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $formatBytes = function ($bytes) {
            $bytes = intval($bytes);
            if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
            if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
            if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
            return $bytes . ' B';
        };

        return response()->json(collect($actives)
            ->filter(fn($a) => (intval($a['bytes-in'] ?? 0) + intval($a['bytes-out'] ?? 0)) > 0)
            ->values()
            ->map(fn($a) => [
                'user'    => $a['user'] ?? '-',
                'address' => $a['address'] ?? '-',
                'uptime'  => $a['uptime'] ?? '-',
                'rx'      => $formatBytes($a['bytes-in'] ?? 0),
                'tx'      => $formatBytes($a['bytes-out'] ?? 0),
            ]));
    }

    /**
     * API: Hotspot users (JSON)
     */
    public function apiHotspotUsers(MikrotikService $mt)
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
     * API: System info realtime (JSON)
     */
    public function apiSystemInfo(MikrotikService $mt)
    {
        try {
            $resource = $mt->getSystemResource();
            $identity = $mt->getIdentity();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        // Parse memory
        $totalMem = intval($resource['total-memory'] ?? 0);
        $freeMem = intval($resource['free-memory'] ?? 0);
        $usedMem = $totalMem - $freeMem;
        $memPercent = $totalMem > 0 ? round(($usedMem / $totalMem) * 100) : 0;

        // Parse HDD
        $totalHdd = intval($resource['total-hdd-space'] ?? 0);
        $freeHdd = intval($resource['free-hdd-space'] ?? 0);
        $usedHdd = $totalHdd - $freeHdd;
        $hddPercent = $totalHdd > 0 ? round(($usedHdd / $totalHdd) * 100) : 0;

        $formatBytes = function ($bytes) {
            if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
            if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
            if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
            return $bytes . ' B';
        };

        return response()->json([
            'identity'       => $identity,
            'board'          => $resource['board-name'] ?? '-',
            'version'        => $resource['version'] ?? '-',
            'uptime'         => $resource['uptime'] ?? '-',
            'cpu'            => $resource['cpu'] ?? '-',
            'cpuLoad'        => intval($resource['cpu-load'] ?? 0),
            'cpuCount'       => $resource['cpu-count'] ?? '1',
            'architecture'   => $resource['architecture-name'] ?? '-',
            'totalMemory'    => $formatBytes($totalMem),
            'usedMemory'     => $formatBytes($usedMem),
            'freeMemory'     => $formatBytes($freeMem),
            'memPercent'     => $memPercent,
            'totalHdd'       => $formatBytes($totalHdd),
            'usedHdd'        => $formatBytes($usedHdd),
            'freeHdd'        => $formatBytes($freeHdd),
            'hddPercent'     => $hddPercent,
        ]);
    }

    /**
     * API: User stats realtime (JSON) for chart
     */
    public function apiUserStats(MikrotikService $mt)
    {
        try {
            $users = $mt->getHotspotUsers();
            $activeUsers = $mt->getActiveUsers();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $total = count($users);
        $active = count($activeUsers);
        $disabled = collect($users)->where('disabled', 'true')->count();
        $enabled = $total - $disabled;

        return response()->json([
            'total'    => $total,
            'online'   => $active,
            'enabled'  => $enabled,
            'disabled' => $disabled,
            'time'     => now()->format('H:i:s'),
        ]);
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

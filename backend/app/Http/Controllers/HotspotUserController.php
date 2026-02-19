<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHotspotUserRequest;
use App\Http\Requests\BulkDeleteRequest;
use App\Http\Requests\ResetHotspotPasswordRequest;
use App\Services\MikrotikService;
use App\Services\MikrotikCacheService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class HotspotUserController extends Controller
{
    /**
     * Tampilkan semua user hotspot
     * Data users & profiles di-load via JSON API agar halaman lebih ringan
     */
    public function index()
    {
        return view('hotspot.index');
    }

    /**
     * Tambah user hotspot
     */
    public function store(StoreHotspotUserRequest $request, MikrotikService $mt, MikrotikCacheService $cache)
    {
        try {
            $mt->addHotspotUser($request->validated());
            $cache->invalidateUserCaches();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambah user hotspot: ' . $e->getMessage());
        }

        return back()->with('success', 'User hotspot berhasil ditambahkan');
    }

    /**
     * Upload user hotspot dari file XLSX
     */
    public function uploadXlsx(Request $request, MikrotikService $mt, MikrotikCacheService $cache)
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

        // Invalidate user caches setelah upload
        if ($successCount > 0) {
            $cache->invalidateUserCaches();
        }

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
    public function destroy(string $id, MikrotikService $mt, MikrotikCacheService $cache)
    {
        try {
            $mt->deleteHotspotUser($id);
            $cache->invalidateUserCaches();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user hotspot: ' . $e->getMessage());
        }

        return back()->with('success', 'User hotspot berhasil dihapus');
    }

    /**
     * Hapus banyak user hotspot sekaligus
     */
    public function bulkDestroy(BulkDeleteRequest $request, MikrotikService $mt, MikrotikCacheService $cache)
    {
        $success = 0;
        $failed  = 0;

        foreach ($request->validated()['ids'] as $id) {
            try {
                $mt->deleteHotspotUser($id);
                $success++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        if ($success > 0) {
            $cache->invalidateUserCaches();
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
    public function resetPassword(ResetHotspotPasswordRequest $request, string $id, MikrotikService $mt, MikrotikCacheService $cache)
    {
        try {
            $mt->resetPassword($id, $request->validated()['password']);
            $cache->invalidateUserCaches();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal reset password: ' . $e->getMessage());
        }

        return back()->with('success', 'Password berhasil direset');
    }

    /**
     * Disable user hotspot + kick dari jaringan
     */
    public function disable(string $id, MikrotikService $mt, MikrotikCacheService $cache)
    {
        try {
            $username = $mt->getUsernameById($id);
            $mt->disableUser($id);
            if ($username) {
                $mt->kickActiveUser($username);
            }
            $cache->invalidateUserCaches();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal disable user: ' . $e->getMessage());
        }

        return back()->with('success', 'User berhasil di-disable & terputus dari jaringan');
    }

    /**
     * Enable user hotspot
     */
    public function enable(string $id, MikrotikService $mt, MikrotikCacheService $cache)
    {
        try {
            $mt->enableUser($id);
            $cache->invalidateUserCaches();
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
    public function destroyProfile(string $id, MikrotikService $mt, MikrotikCacheService $cache)
    {
        try {
            $mt->deleteProfile($id);
            $cache->invalidate('profiles', 'user_stats');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus profile: ' . $e->getMessage());
        }

        return back()->with('success', 'Profile berhasil dihapus');
    }
}

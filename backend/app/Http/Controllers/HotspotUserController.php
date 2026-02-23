<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHotspotUserRequest;
use App\Http\Requests\BulkDeleteRequest;
use App\Http\Requests\ResetHotspotPasswordRequest;
use App\Services\MikrotikService;
use App\Services\MikrotikCacheService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Upload user hotspot dari file CSV
     */
    public function uploadCsv(Request $request, MikrotikService $mt, MikrotikCacheService $cache)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->with('error', 'Gagal membaca file CSV.');
        }

        // Baca header baris pertama
        $header = fgetcsv($handle, 0, ',');

        if ($header === false || empty($header)) {
            fclose($handle);
            return back()->with('error', 'File CSV kosong atau tidak memiliki header.');
        }

        // Normalisasi header: lowercase, trim, hapus BOM
        $headerMap = [];
        foreach ($header as $index => $name) {
            $key = strtolower(trim(preg_replace('/\x{FEFF}/u', '', (string) $name)));
            if ($key !== '') {
                $headerMap[$key] = $index;
            }
        }

        $requiredColumns = ['username', 'email', 'password', 'profile'];
        $missing = array_diff($requiredColumns, array_keys($headerMap));
        if (!empty($missing)) {
            fclose($handle);
            return back()->with('error', 'Kolom wajib tidak lengkap: ' . implode(', ', $missing));
        }

        $successCount = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;

            $username = trim((string) ($row[$headerMap['username']] ?? ''));
            $email    = trim((string) ($row[$headerMap['email']] ?? ''));
            $password = trim((string) ($row[$headerMap['password']] ?? ''));
            $profile  = trim((string) ($row[$headerMap['profile']] ?? ''));

            // Skip baris kosong
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
                    'name'     => $username,
                    'password' => $password,
                    'profile'  => $profile,
                    'comment'  => $email,
                ]);
                $successCount++;
            } catch (\Throwable $e) {
                $errors[] = 'Baris ' . $rowNumber . ': ' . $e->getMessage();
            }
        }

        fclose($handle);

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
     * Download template CSV untuk upload user hotspot
     */
    public function downloadTemplate(): StreamedResponse
    {
        $filename = 'template_hotspot_users.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // Tulis BOM agar Excel membaca UTF-8 dengan benar
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header kolom
            fputcsv($handle, ['username', 'email', 'password', 'profile']);

            // Contoh data
            fputcsv($handle, ['anakmagang01', 'anakmagang01@example.com', 'pass1234', '@mahasiswa']);
            fputcsv($handle, ['dosenmagang01', 'dosenmagang01@example.com', 'pass1234', '@dosen']);
            fputcsv($handle, ['mahasiswamagang01', 'mahasiswamagang01@example.com', 'pass1234', '@mahasiswa']);
            fputcsv($handle, ['staffmagang01', 'staffmagang01@example.com', 'pass1234', '@staff']);
            fputcsv($handle, ['tamumagang01', 'tamumagang01@example.com', 'pass1234', 'IT']);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
     * Monitoring user aktif (halaman terpisah)
     */
    public function monitoring()
    {
        return view('hotspot.monitoring');
    }

    /**
     * Cut off / disconnect active user dari jaringan
     */
    public function cutoff(string $username, MikrotikService $mt, MikrotikCacheService $cache)
    {
        try {
            $kicked = $mt->kickActiveUser($username);
            $cache->invalidate('active_users', 'user_stats');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal cut off user: ' . $e->getMessage());
        }

        return back()->with('success', "User '{$username}' berhasil di-cut off dari jaringan");
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

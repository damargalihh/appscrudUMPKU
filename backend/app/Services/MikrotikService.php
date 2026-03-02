<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;

class MikrotikService
{
    protected ?Client $client = null;
    protected bool $connectionFailed = false;
    protected ?string $lastError = null;

    /**
     * Lazy connection — koneksi baru dibuat saat pertama kali dipanggil.
     * Supaya error bisa ditangkap oleh try-catch di controller.
     */
    protected function client(): Client
    {
        if ($this->client !== null) {
            return $this->client;
        }

        if ($this->connectionFailed) {
            throw new \RuntimeException('MikroTik connection failed: ' . ($this->lastError ?? 'Unknown error'));
        }

        $attempts = (int) config('mikrotik.attempts', 2);
        $timeout  = (int) config('mikrotik.timeout', 15);
        $lastException = null;

        for ($i = 0; $i < $attempts; $i++) {
            try {
                $this->client = new Client([
                    'host'    => config('mikrotik.host'),
                    'user'    => config('mikrotik.user'),
                    'pass'    => config('mikrotik.pass'),
                    'port'    => (int) config('mikrotik.port'),
                    'timeout' => $timeout,
                ]);

                return $this->client;
            } catch (\Exception $e) {
                $lastException = $e;
                // Tunggu sebentar sebelum retry
                if ($i < $attempts - 1) {
                    usleep(500000); // 0.5 detik
                }
            }
        }

        $this->connectionFailed = true;
        $this->lastError = $lastException?->getMessage() ?? 'Unknown error';
        throw $lastException;
    }

    /**
     * Cek response dari RouterOS API — jika ada !trap, lempar exception.
     */
    protected function checkResponse(array $response): array
    {
        foreach ($response as $item) {
            if (isset($item['!trap'])) {
                $message = $item['message'] ?? 'Unknown MikroTik error';
                throw new \RuntimeException($message);
            }
        }

        return $response;
    }

    /**
     * TEST KONEKSI
     */
    public function test()
    {
        return $this->client()->query(
            new Query('/system/resource/print')
        )->read();
    }

    /**
     * AMBIL SEMUA USER HOTSPOT
     */
    public function getHotspotUsers()
    {
        return $this->client()->query(
            new Query('/ip/hotspot/user/print')
        )->read();
    }

    /**
     * TAMBAH USER HOTSPOT
     */
    public function addHotspotUser(array $data)
    {
        $query = (new Query('/ip/hotspot/user/add'))
            ->equal('name', $data['name'])
            ->equal('password', $data['password'])
            ->equal('profile', $data['profile'] ?? 'default');

        if (!empty($data['comment'])) {
            $query->equal('comment', $data['comment']);
        }

        $response = $this->client()->query($query)->read();

        return $this->checkResponse($response);
    }

    /**
     * HAPUS USER HOTSPOT
     */
    public function deleteHotspotUser(string $id)
    {
        $response = $this->client()->query(
            (new Query('/ip/hotspot/user/remove'))
                ->equal('.id', $id)
        )->read();

        return $this->checkResponse($response);
    }

    /**
     * AMBIL PROFILE HOTSPOT
     */
    public function getProfiles()
    {
        return $this->client()->query(
            new Query('/ip/hotspot/user/profile/print')
        )->read();
    }

    /**
     * AMBIL DAFTAR NAMA PROFILE YANG VALID
     */
    public function getProfileNames(): array
    {
        $profiles = $this->getProfiles();

        return collect($profiles)->pluck('name')->filter()->values()->all();
    }

    /**
     * RESET PASSWORD USER
     */
    public function resetPassword(string $id, string $newPassword)
    {
        $response = $this->client()->query(
            (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $id)
                ->equal('password', $newPassword)
        )->read();

        return $this->checkResponse($response);
    }

    /**
     * DISABLE USER
     */
    public function disableUser(string $id)
    {
        $response = $this->client()->query(
            (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $id)
                ->equal('disabled', 'yes')
        )->read();

        return $this->checkResponse($response);
    }

    /**
     * KICK USER DARI ACTIVE SESSION (disconnect langsung)
     * Menghapus SEMUA active session, cookie, dan host entry milik username.
     */
    public function kickActiveUser(string $username)
    {
        // Cari semua active session milik username ini
        $actives = $this->client()->query(
            (new Query('/ip/hotspot/active/print'))
                ->where('user', $username)
        )->read();

        // Kumpulkan MAC address sebelum remove session
        $macAddresses = [];
        foreach ($actives as $session) {
            if (!empty($session['mac-address'])) {
                $macAddresses[] = $session['mac-address'];
            }
        }

        // Remove setiap active session
        foreach ($actives as $session) {
            if (isset($session['.id'])) {
                $this->client()->query(
                    (new Query('/ip/hotspot/active/remove'))
                        ->equal('.id', $session['.id'])
                )->read();
            }
        }

        // Hapus cookie hotspot milik username
        $this->removeCookiesByUser($username);

        // Hapus host entry + temporary block per MAC agar redirect ke login page
        foreach (array_unique($macAddresses) as $mac) {
            $this->removeHostByMac($mac);
            $this->temporaryBlockMac($mac);
        }

        return count($actives);
    }

    /**
     * KICK SATU ACTIVE SESSION berdasarkan session .id
     * Menghapus session + cookie + host entry + temporary block
     * agar device langsung kehilangan koneksi dan redirect ke login page.
     */
    public function kickActiveSession(string $sessionId)
    {
        // Ambil info session dulu (untuk dapat MAC address & username)
        $sessions = $this->client()->query(
            (new Query('/ip/hotspot/active/print'))
                ->where('.id', $sessionId)
        )->read();

        $username = $sessions[0]['user'] ?? null;
        $mac = $sessions[0]['mac-address'] ?? null;

        // 1. Hapus active session
        $this->client()->query(
            (new Query('/ip/hotspot/active/remove'))
                ->equal('.id', $sessionId)
        )->read();

        // 2. Hapus cookie hotspot milik user + MAC ini
        if ($username) {
            $this->removeCookiesByUser($username, $mac);
        }

        // 3. Hapus host entry
        if ($mac) {
            $this->removeHostByMac($mac);
        }

        // 4. Temporary block MAC via ip-binding → force device captive portal re-detection
        if ($mac) {
            $this->temporaryBlockMac($mac);
        }

        return true;
    }

    /**
     * Temporary block MAC via ip-binding lalu hapus.
     *
     * Alur:
     *   1. Block MAC → device kehilangan semua traffic
     *   2. Tunggu 3 detik → cukup bagi OS (Android/iOS) mendeteksi "no internet"
     *   3. Hapus block → device reconnect, OS kirim captive portal probe (HTTP)
     *   4. MikroTik intercept probe → redirect ke halaman login hotspot
     *
     * 3 detik diperlukan karena:
     *   - Android cek konektivitas tiap 1-2 detik
     *   - iOS cek tiap 2-3 detik
     *   - Kurang dari itu, OS belum sempat menandai "no internet"
     */
    public function temporaryBlockMac(string $mac)
    {
        $bindingId = null;

        try {
            // Tambah ip-binding blocked sementara
            $result = $this->client()->query(
                (new Query('/ip/hotspot/ip-binding/add'))
                    ->equal('mac-address', $mac)
                    ->equal('type', 'blocked')
                    ->equal('comment', 'auto-cutoff-temp')
            )->read();

            // Simpan ID dari binding yang baru dibuat untuk removal yang cepat
            $bindingId = $result[0]['ret'] ?? null;

            // Tunggu 3 detik agar OS device mendeteksi kehilangan koneksi
            sleep(3);

            // Hapus ip-binding sementara agar user bisa login ulang
            if ($bindingId) {
                // Hapus langsung berdasarkan ID (lebih cepat)
                $this->client()->query(
                    (new Query('/ip/hotspot/ip-binding/remove'))
                        ->equal('.id', $bindingId)
                )->read();
            } else {
                // Fallback: cari berdasarkan MAC + comment
                $this->cleanupTempBindings($mac);
            }
        } catch (\Exception $e) {
            // Bersihkan binding agar user tidak ter-block permanen
            if ($bindingId) {
                try {
                    $this->client()->query(
                        (new Query('/ip/hotspot/ip-binding/remove'))
                            ->equal('.id', $bindingId)
                    )->read();
                } catch (\Exception $e2) {
                    $this->cleanupTempBindings($mac);
                }
            } else {
                $this->cleanupTempBindings($mac);
            }
        }
    }

    /**
     * Bersihkan ip-binding sementara (failsafe).
     */
    protected function cleanupTempBindings(string $mac)
    {
        try {
            $bindings = $this->client()->query(
                (new Query('/ip/hotspot/ip-binding/print'))
                    ->where('mac-address', $mac)
                    ->where('comment', 'auto-cutoff-temp')
            )->read();

            foreach ($bindings as $binding) {
                if (isset($binding['.id'])) {
                    $this->client()->query(
                        (new Query('/ip/hotspot/ip-binding/remove'))
                            ->equal('.id', $binding['.id'])
                    )->read();
                }
            }
        } catch (\Exception $e) {
            // Log error tapi jangan throw — ini sudah failsafe
        }
    }

    /**
     * Hapus cookie hotspot berdasarkan username (dan opsional MAC tertentu).
     */
    public function removeCookiesByUser(string $username, ?string $mac = null)
    {
        $query = (new Query('/ip/hotspot/cookie/print'))
            ->where('user', $username);

        $cookies = $this->client()->query($query)->read();

        foreach ($cookies as $cookie) {
            if (!isset($cookie['.id'])) continue;

            // Jika MAC diberikan, hanya hapus cookie yang cocok
            if ($mac !== null && ($cookie['mac-address'] ?? '') !== $mac) {
                continue;
            }

            $this->client()->query(
                (new Query('/ip/hotspot/cookie/remove'))
                    ->equal('.id', $cookie['.id'])
            )->read();
        }
    }

    /**
     * Hapus host entry berdasarkan MAC address.
     * Ini membuat device kehilangan status "authorized" dan di-redirect ke login page.
     */
    public function removeHostByMac(string $mac)
    {
        $hosts = $this->client()->query(
            (new Query('/ip/hotspot/host/print'))
                ->where('mac-address', $mac)
        )->read();

        foreach ($hosts as $host) {
            if (isset($host['.id'])) {
                $this->client()->query(
                    (new Query('/ip/hotspot/host/remove'))
                        ->equal('.id', $host['.id'])
                )->read();
            }
        }
    }

    /**
     * GET USERNAME BY HOTSPOT USER ID
     */
    public function getUsernameById(string $id)
    {
        $result = $this->client()->query(
            (new Query('/ip/hotspot/user/print'))
                ->where('.id', $id)
        )->read();

        return $result[0]['name'] ?? null;
    }

    /**
     * ENABLE USER
     */
    public function enableUser(string $id)
    {
        $response = $this->client()->query(
            (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $id)
                ->equal('disabled', 'no')
        )->read();

        return $this->checkResponse($response);
    }

    /**
     * HAPUS PROFILE HOTSPOT
     */
    public function deleteProfile(string $id)
    {
        $response = $this->client()->query(
            (new Query('/ip/hotspot/user/profile/remove'))
                ->equal('.id', $id)
        )->read();

        return $this->checkResponse($response);
    }

    /**
     * USER HOTSPOT AKTIF
     */
    public function getActiveUsers()
    {
        return $this->client()->query(
            new Query('/ip/hotspot/active/print')
        )->read();
    }

    /**
     * AMBIL QUEUE / BANDWIDTH USAGE
     */
    public function getQueues()
    {
        return $this->client()->query(
            new Query('/queue/simple/print')
        )->read();
    }

    /**
     * AMBIL SYSTEM RESOURCE (CPU, Memory, Uptime, dll)
     */
    public function getSystemResource()
    {
        $result = $this->client()->query(
            new Query('/system/resource/print')
        )->read();

        return $result[0] ?? [];
    }

    /**
     * AMBIL SYSTEM IDENTITY
     */
    public function getIdentity()
    {
        $result = $this->client()->query(
            new Query('/system/identity/print')
        )->read();

        return $result[0]['name'] ?? 'MikroTik';
    }

    /**
     * CARI USER HOTSPOT BERDASARKAN EMAIL
     * Mencari di field 'name' dan 'comment' hotspot user
     */
    public function findHotspotUserByEmail(string $email): ?array
    {
        $users = $this->getHotspotUsers();
        $emailLower = strtolower(trim($email));

        foreach ($users as $user) {
            // Cek apakah email cocok dengan field 'name'
            if (isset($user['name']) && strtolower(trim($user['name'])) === $emailLower) {
                return $user;
            }
            // Cek apakah email ada di field 'comment'
            if (isset($user['comment']) && str_contains(strtolower($user['comment']), $emailLower)) {
                return $user;
            }
        }

        return null;
    }

    /**
     * CONNECT USER HOTSPOT VIA GOOGLE OAUTH
     * Mencocokkan email Google dengan user hotspot MikroTik,
     * lalu mengaktifkan koneksi via IP-binding bypass untuk MAC address user.
     */
    public function connectUser(string $email, ?string $macAddress = null): array
    {
        $hotspotUser = $this->findHotspotUserByEmail($email);

        if (!$hotspotUser) {
            throw new \RuntimeException('Email tidak ditemukan di daftar user hotspot MikroTik.');
        }

        // Jika user disabled, aktifkan dulu
        if (($hotspotUser['disabled'] ?? 'false') === 'true') {
            $this->enableUser($hotspotUser['.id']);
        }

        // Jika MAC address tersedia, buat IP-binding bypass agar user langsung terkoneksi
        if ($macAddress) {
            $this->removeGoogleOAuthBindings($macAddress);

            $this->client()->query(
                (new Query('/ip/hotspot/ip-binding/add'))
                    ->equal('mac-address', $macAddress)
                    ->equal('type', 'bypassed')
                    ->equal('comment', 'google-oauth:' . $email)
            )->read();
        }

        return $hotspotUser;
    }

    /**
     * HAPUS IP-BINDING GOOGLE OAUTH LAMA BERDASARKAN MAC
     */
    public function removeGoogleOAuthBindings(string $macAddress): void
    {
        try {
            $bindings = $this->client()->query(
                (new Query('/ip/hotspot/ip-binding/print'))
                    ->where('mac-address', $macAddress)
            )->read();

            foreach ($bindings as $binding) {
                if (
                    isset($binding['.id']) &&
                    isset($binding['comment']) &&
                    str_starts_with($binding['comment'], 'google-oauth:')
                ) {
                    $this->client()->query(
                        (new Query('/ip/hotspot/ip-binding/remove'))
                            ->equal('.id', $binding['.id'])
                    )->read();
                }
            }
        } catch (\Exception $e) {
            // Abaikan error pembersihan — bukan critical
        }
    }

    /**
     * AMBIL HOST INFO BERDASARKAN IP ADDRESS
     */
    public function getHostByIp(string $ip): ?array
    {
        $hosts = $this->client()->query(
            (new Query('/ip/hotspot/host/print'))
                ->where('address', $ip)
        )->read();

        return $hosts[0] ?? null;
    }
}
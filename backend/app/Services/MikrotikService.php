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
     */
    public function kickActiveUser(string $username)
    {
        // Cari semua active session milik username ini
        $actives = $this->client()->query(
            (new Query('/ip/hotspot/active/print'))
                ->where('user', $username)
        )->read();

        // Remove setiap active session
        foreach ($actives as $session) {
            if (isset($session['.id'])) {
                $this->client()->query(
                    (new Query('/ip/hotspot/active/remove'))
                        ->equal('.id', $session['.id'])
                )->read();
            }
        }

        return count($actives);
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
}
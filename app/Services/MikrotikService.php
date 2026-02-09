<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;

class MikrotikService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'host'    => config('mikrotik.host'),
            'user'    => config('mikrotik.user'),
            'pass'    => config('mikrotik.pass'),
            'port'    => (int) config('mikrotik.port'),
            'timeout' => 3,
        ]);
    }

    /**
     * TEST KONEKSI
     */
    public function test()
    {
        return $this->client->query(
            new Query('/system/resource/print')
        )->read();
    }

    /**
     * AMBIL SEMUA USER HOTSPOT
     */
    public function getHotspotUsers()
    {
        return $this->client->query(
            new Query('/ip/hotspot/user/print')
        )->read();
    }

    /**
     * TAMBAH USER HOTSPOT
     */
    public function addHotspotUser(array $data)
    {
        return $this->client->query(
            (new Query('/ip/hotspot/user/add'))
                ->equal('name', $data['name'])
                ->equal('password', $data['password'])
                ->equal('profile', $data['profile'] ?? 'default')
        )->read();
    }

    /**
     * HAPUS USER HOTSPOT
     */
    public function deleteHotspotUser(string $id)
    {
        return $this->client->query(
            (new Query('/ip/hotspot/user/remove'))
                ->equal('.id', $id)
        )->read();
    }

    /**
     * AMBIL PROFILE HOTSPOT
     */
    public function getProfiles()
    {
        return $this->client->query(
            new Query('/ip/hotspot/user/profile/print')
        )->read();
    }

    /**
     * RESET PASSWORD USER
     */
    public function resetPassword(string $id, string $newPassword)
    {
        return $this->client->query(
            (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $id)
                ->equal('password', $newPassword)
        )->read();
    }

    /**
     * DISABLE USER
     */
    public function disableUser(string $id)
    {
        return $this->client->query(
            (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $id)
                ->equal('disabled', 'yes')
        )->read();
    }

    /**
     * ENABLE USER
     */
    public function enableUser(string $id)
    {
        return $this->client->query(
            (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $id)
                ->equal('disabled', 'no')
        )->read();
    }

    /**
     * USER HOTSPOT AKTIF
     */
    public function getActiveUsers()
    {
        return $this->client->query(
            new Query('/ip/hotspot/active/print')
        )->read();
    }
}

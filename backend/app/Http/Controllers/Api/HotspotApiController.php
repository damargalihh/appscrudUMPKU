<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MikrotikCacheService;

class HotspotApiController extends Controller
{
    protected function respond(array $result)
    {
        return response()->json($result['data'])
            ->header('X-Data-Cached', ($result['cached'] ?? false) ? 'true' : 'false')
            ->header('X-Data-FetchedAt', $result['fetched_at'] ?? '')
            ->header('X-Data-Empty', ($result['empty'] ?? false) ? 'true' : 'false');
    }

    public function profiles(MikrotikCacheService $cache)
    {
        return $this->respond($cache->getProfiles());
    }

    public function activeUsers(MikrotikCacheService $cache)
    {
        return $this->respond($cache->getActiveUsers());
    }

    public function hotspotUsers(MikrotikCacheService $cache)
    {
        return $this->respond($cache->getHotspotUsers());
    }

    public function systemInfo(MikrotikCacheService $cache)
    {
        return $this->respond($cache->getSystemInfo());
    }

    public function userStats(MikrotikCacheService $cache)
    {
        return $this->respond($cache->getUserStats());
    }

    public function bandwidth(MikrotikCacheService $cache)
    {
        return $this->respond($cache->getBandwidth());
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikrotikCache extends Model
{
    protected $table = 'mikrotik_cache';

    protected $fillable = [
        'key',
        'data',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'fetched_at' => 'datetime',
        ];
    }

    /**
     * Get decoded JSON data
     */
    public function getDecodedDataAttribute(): mixed
    {
        return json_decode($this->data, true);
    }

    /**
     * Store data for a given key (insert or update)
     */
    public static function store(string $key, mixed $data): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'data'       => json_encode($data),
                'fetched_at' => now(),
            ]
        );
    }

    /**
     * Get cached data for a given key
     */
    public static function getCached(string $key): ?array
    {
        $cache = static::where('key', $key)->first();

        if (!$cache) {
            return null;
        }

        return [
            'data'       => $cache->decoded_data,
            'fetched_at' => $cache->fetched_at,
        ];
    }
}

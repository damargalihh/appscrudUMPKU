<?php

return [
    'host'    => env('MIKROTIK_HOST', '192.168.200.1'),
    'user'    => env('MIKROTIK_USER', 'apiadmin'),
    'pass'    => env('MIKROTIK_PASS', 'admin'),
    'port'    => env('MIKROTIK_PORT', 8728),
    'timeout' => env('MIKROTIK_TIMEOUT', 15),
    'attempts' => env('MIKROTIK_ATTEMPTS', 2),
];

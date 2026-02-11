<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format bytes menjadi satuan yang mudah dibaca (B, KB, MB, GB).
     */
    public static function bytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}

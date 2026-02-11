<?php

namespace App\Http\Controllers;

use App\Services\MikrotikService;

class TestMikrotikController extends Controller
{
    public function index(MikrotikService $mt)
    {
        try {
            $result = $mt->test();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil terhubung ke MikroTik',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke MikroTik',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HotspotUserController;
use App\Http\Controllers\Api\HotspotApiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelfRegisterController;
use App\Http\Controllers\TestMikrotikController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Redirect ke login
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Routes yang membutuhkan autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Hotspot users CRUD
    Route::prefix('hotspot-users')->name('hotspot.')->group(function () {
        Route::get('/', [HotspotUserController::class, 'index'])->name('index');
        Route::post('/', [HotspotUserController::class, 'store'])->name('store');
        Route::post('/upload', [HotspotUserController::class, 'uploadCsv'])->name('upload');
        Route::get('/download-template', [HotspotUserController::class, 'downloadTemplate'])->name('downloadTemplate');
        Route::post('/bulk-delete', [HotspotUserController::class, 'bulkDestroy'])->name('bulkDestroy');
        Route::delete('/{id}', [HotspotUserController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/reset-password', [HotspotUserController::class, 'resetPassword'])->name('resetPassword');
        Route::post('/{id}/disable', [HotspotUserController::class, 'disable'])->name('disable');
        Route::post('/{id}/enable', [HotspotUserController::class, 'enable'])->name('enable');
        Route::post('/cutoff/{sessionId}', [HotspotUserController::class, 'cutoff'])->name('cutoff');
    });

    Route::get('/hotspot-active', [HotspotUserController::class, 'active'])->name('hotspot.active');
    Route::get('/monitoring-users', [HotspotUserController::class, 'monitoring'])->name('hotspot.monitoring');
    Route::delete('/hotspot-profiles/{id}', [HotspotUserController::class, 'destroyProfile'])->name('hotspot.destroyProfile');

    // API endpoints (JSON) untuk dashboard realtime
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/bandwidth', [HotspotApiController::class, 'bandwidth'])->name('bandwidth');
        Route::get('/system-info', [HotspotApiController::class, 'systemInfo'])->name('systemInfo');
        Route::get('/user-stats', [HotspotApiController::class, 'userStats'])->name('userStats');
        Route::get('/profiles', [HotspotApiController::class, 'profiles'])->name('profiles');
        Route::get('/hotspot-users', [HotspotApiController::class, 'hotspotUsers'])->name('hotspotUsers');
        Route::get('/active-users', [HotspotApiController::class, 'activeUsers'])->name('activeUsers');
    });
});

/*
|--------------------------------------------------------------------------
| Self-registration hotspot (publik, tanpa login)
|--------------------------------------------------------------------------
*/
Route::prefix('register-hotspot')->group(function () {
    Route::get('/dosen', [SelfRegisterController::class, 'showForm'])->defaults('role', 'dosen');
    Route::get('/mahasiswa', [SelfRegisterController::class, 'showForm'])->defaults('role', 'mahasiswa');
    Route::get('/staff', [SelfRegisterController::class, 'showForm'])->defaults('role', 'staff');
    Route::get('/tamu', [SelfRegisterController::class, 'showForm'])->defaults('role', 'tamu');
    Route::post('/', [SelfRegisterController::class, 'selfRegister'])->name('hotspot.selfRegister');
});

/*
|--------------------------------------------------------------------------
| Debug / Test (nonaktifkan di production)
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    Route::get('/test-mt', [TestMikrotikController::class, 'index']);
}

require __DIR__ . '/auth.php';

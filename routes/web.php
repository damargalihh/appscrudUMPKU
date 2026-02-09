<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HotspotUserController;
use App\Http\Controllers\TestMikrotikController;
use App\Http\Controllers\SelfRegisterController;

Route::get('/test-mt', [TestMikrotikController::class, 'index']);

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::middleware(['auth', 'verified'])->get('/dashboard', [HotspotUserController::class, 'dashboard'])->name('dashboard');

});

Route::middleware(['auth'])->group(function () {
    Route::get('/hotspot-users', [HotspotUserController::class, 'index'])->name('hotspot.index');
    Route::post('/hotspot-users', [HotspotUserController::class, 'store'])->name('hotspot.store');
    Route::delete('/hotspot-users/{id}', [HotspotUserController::class, 'destroy'])->name('hotspot.destroy');
    Route::post('/hotspot-users/{id}/reset-password', [HotspotUserController::class, 'resetPassword'])->name('hotspot.resetPassword');
    Route::post('/hotspot-users/{id}/disable', [HotspotUserController::class, 'disable']);
    Route::post('/hotspot-users/{id}/enable', [HotspotUserController::class, 'enable']);
    Route::get('/hotspot-active', [HotspotUserController::class, 'active']);
    Route::delete('/hotspot-profiles/{id}', [HotspotUserController::class, 'destroyProfile'])->name('hotspot.destroyProfile');
    Route::get('/api/bandwidth', [HotspotUserController::class, 'apiBandwidth'])->name('api.bandwidth');
    Route::get('/api/system-info', [HotspotUserController::class, 'apiSystemInfo'])->name('api.systemInfo');
    Route::get('/api/user-stats', [HotspotUserController::class, 'apiUserStats'])->name('api.userStats');
    Route::get('/api/profiles', [HotspotUserController::class, 'apiProfiles'])->name('api.profiles');
    Route::get('/api/active-users', [HotspotUserController::class, 'apiActiveUsers'])->name('api.activeUsers');
});

Route::get('/register-hotspot', [SelfRegisterController::class, 'showRegister']);
Route::post('/register-hotspot', [SelfRegisterController::class, 'selfRegister'])
    ->name('hotspot.selfRegister');

require __DIR__.'/auth.php';

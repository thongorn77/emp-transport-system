<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\DriverController;

Route::get('/', function () { return redirect()->route('dashboard'); });

Route::get('/display/{fac}', [SecurityController::class, 'display']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin (ต้อง login)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/report',             [AdminController::class, 'index'])->name('admin.report');
    Route::get('/admin/drivers',            [AdminController::class, 'driverList'])->name('admin.drivers');
    Route::post('/admin/approve/{id}',      [AdminController::class, 'approveDriver'])->name('admin.approve');

    Route::get('/admin/buses',              [AdminController::class, 'busesList'])->name('admin.buses');
    Route::post('/admin/buses',             [AdminController::class, 'busStore'])->name('admin.buses.store');
    Route::put('/admin/buses/{id}',         [AdminController::class, 'busUpdate'])->name('admin.buses.update');
    Route::delete('/admin/buses/{id}',      [AdminController::class, 'busDestroy'])->name('admin.buses.destroy');

    Route::get('/admin/logs',               [AdminController::class, 'logsList'])->name('admin.logs');
});

// ─── Driver: สาธารณะ (ไม่ต้อง auth) ─────────────────────────────────────────
Route::get('/driver/register',          [DriverController::class, 'showRegisterForm'])->name('driver.register');
Route::post('/driver/register',         [DriverController::class, 'storeDriver'])->name('driver.storeDriver');
Route::post('/driver/check-status',     [DriverController::class, 'checkStatus']);
Route::post('/driver/auth',             [DriverController::class, 'auth'])->name('driver.auth');

// ─── Driver: Protected (ต้องมี cookie) ───────────────────────────────────────
Route::middleware('driver.auth')->group(function () {
    Route::get('/driver',               [DriverController::class, 'dashboard'])->name('driver.dashboard');
    Route::get('/driver/checkin',       [DriverController::class, 'index'])->name('driver.checkin');
    Route::get('/driver/profile',       [DriverController::class, 'profile'])->name('driver.profile');
    Route::post('/driver/my-buses',     [DriverController::class, 'myBuses']);
});

// ─── Legacy redirects (สำหรับ link เก่าที่แชร์ใน LINE) ───────────────────────
Route::get('/register-driver',          fn() => redirect()->route('driver.register'));
Route::post('/register-driver',         [DriverController::class, 'storeDriver']);

// ─── Legacy endpoints (backward compat) ──────────────────────────────────────
Route::get('/driver/session-check',          [DriverController::class, 'sessionCheck']);
Route::get('/driver/profile-session',        [DriverController::class, 'profileSession']);
Route::post('/driver/profile-session/clear', [DriverController::class, 'profileSessionClear']);
Route::post('/driver/line-login',            [DriverController::class, 'lineLogin']);

require __DIR__.'/auth.php';

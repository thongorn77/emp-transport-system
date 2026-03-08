<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SecurityController;

Route::get('/display/{fac}', [SecurityController::class, 'display']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// เฉพาะคนที่ Login แล้วเท่านั้นถึงจะดู Report ได้
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/report', [AdminController::class, 'index']);
    Route::get('/admin/approve-drivers', [AdminController::class, 'driverList']); // หน้ากดยืนยันคนขับ
});
require __DIR__.'/auth.php';

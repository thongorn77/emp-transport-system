<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DriverController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// รับข้อมูล POST จากฟอร์ม
Route::post('/driver/checkin', [DriverController::class, 'store']);
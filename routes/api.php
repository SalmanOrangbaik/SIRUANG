<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\RiwayatController;
use App\Http\Controllers\Api\RuanganController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/ruangan', [RuanganController::class, 'index']);
Route::get('/ruangan/{id}', [RuanganController::class, 'show']);

//  route booking  dalam middleware auth
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/jadwal', [JadwalController::class, 'index']);
    Route::get('/jadwal/{id}', [JadwalController::class, 'show']);

    //  route booking harus dalam auth
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::post('/booking', [BookingController::class, 'store']);

    // route riwayat booking user
    Route::get('/riwayat', [RiwayatController::class, 'index']);
});

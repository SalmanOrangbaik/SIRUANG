<?php

use App\Http\Middleware\Admin;
use App\Http\Controllers\BackendController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\RuanganController;
use App\Http\Controllers\Backend\JadwalController;
use App\Http\Controllers\Backend\BookingController;
use App\Http\Controllers\BookingUserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

Route::get('/', [FrontendController::class, 'index']);
Route::get('booking/riwayat', [FrontendController::class, 'riwayat'])->name('booking_riwayat');
Route::get('booking/ruangan', [FrontendController::class, 'ruangan'])->name('booking_ruangan');
Route::get('booking/ruangan/{id}', [FrontendController::class, 'detailRuangan'])->name('detail_ruangan');

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/booking/create', [BookingUserController::class, 'create'])->name('booking.create');
    Route::post('/booking', [BookingUserController::class, 'store'])->name('booking.store');
    Route::put('/booking/{booking}', [BookingUserController::class, 'update'])->name('booking.update');
});
Route::get('booking-export', [BookingController::class, 'export'])->name('booking.export');


Route::get('/images/{path}', function ($path) {
    $storagePath = storage_path('app/public/' . $path);

    if (! File::exists($storagePath)) {
        abort(404);
    }

    $file = File::get($storagePath);
    $type = File::mimeType($storagePath);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);
    $response->header("Access-Control-Allow-Origin", "*");
    $response->header("Access-Control-Allow-Methods", "GET");
    $response->header("Access-Control-Allow-Headers", "Content-Type");

    return $response;
})->where('path', '.*');


//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Route Admin dan Backend
Route::group(['prefix' => 'admin', 'as' => 'backend.', 'middleware' => ['auth', Admin::class]], function() {
    Route::get('/', [BackendController::class, 'index'])->name('index');
    Route::resource('user', UserController::class);
    Route::resource('ruang', RuanganController::class);
    Route::resource('jadwal', JadwalController::class);
    Route::resource('booking', BookingController::class);
    Route::get('booking-export', [BookingController::class, 'export'])->name('booking.export');

});

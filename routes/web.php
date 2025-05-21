<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RekonbkuController;
use Illuminate\Support\Facades\Artisan;

Route::get('/c', function () {
    Artisan::call('optimize:clear');
    return view('Backend.cache');
})->name('clear.cache');

//Login and Logout
Route::get('/login', [LoginController::class, 'showForm'])->name('login');
Route::post('/login', [LoginController::class, 'loginApi'])->name('login.submit');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth.session'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [HomeController::class, 'home'])->name('index');

    Route::get('/persediaanpdopd', [NotifikasiController::class, 'show'])->name('persediaanpdopd');
    Route::get('/persediaansekolah', [NotifikasiController::class, 'showSekolah'])->name('persediaansekolah');
    Route::get('/persediaanblud', [NotifikasiController::class, 'showBlud'])->name('persediaanblud');

    Route::get('/data-api', [NotifikasiController::class, 'getData'])->name('api');
    Route::post('/rekonbku/store', [RekonbkuController::class, 'store'])->name('rekonbku.store');
    Route::post('/rekonbku/update', [RekonbkuController::class, 'update'])->name('rekonbku.update');

    Route::post('/rekonbku/storeBelum', [RekonbkuController::class, 'storebelumRekon'])->name('rekonbku.belum');
    Route::post('/rekonbku/updateBelum', [RekonbkuController::class, 'updatebelumRekon'])->name('rekonbku.updatebelum');

});

Route::fallback(function () {
    return view('Backend.notfound'); // 404 page
});


<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('datatable-data', [HomeController::class, 'getData'])->name('datatable.data');
Route::get('/not-found', [HomeController::class, 'notFound'])->name('not-found');

Route::get('notifikasi', [NotifikasiController::class, 'show'])->name('notifikasi');


Route::get('/login', [LoginController::class, 'showForm'])->name('login');



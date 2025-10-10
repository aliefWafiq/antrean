<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\antreanController;
use App\Http\Controllers\SmsController;

Route::get( '/login', [antreanController::class, 'loginView'])->name('login');
Route::get('/', [antreanController::class, 'home']);
Route::get('/antrean', [antreanController::class, 'antrean'])->name('antrean')->middleware('auth');
Route::get('/ubahJamSidang', [antreanController::class, 'ubahJamSidang'])->name('ubahJamSidang')->middleware('auth');
Route::get('/logout', [antreanController::class, 'logout']);

Route::put('/ambil-antrean/{antrean}', [antreanController::class, 'ambilAntrean']);
Route::put('/action/ubahJamSidang/{antrean}', [antreanController::class, 'editJamSidang']);

Route::get("/sendsms", [SmsController::class, 'sendSms']);

// Route::post('/store/buatAntrean', [antreanController::class, 'store']);
Route::post('/login/action', [antreanController::class, 'login']);

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\antreanController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\pengajuan_jam_sidangController;

Route::get( '/login', [antreanController::class, 'loginView'])->name('login');
Route::get('/', [antreanController::class, 'home']);
Route::get('/antrean', [antreanController::class, 'antrean'])->name('antrean')->middleware('auth');
Route::get('/ubahJamSidang', [pengajuan_jam_sidangController::class, 'ubahJamSidang'])->name('ubahJamSidang')->middleware('auth');
Route::get('/logout', [antreanController::class, 'logout']);
Route::get('/verify-otp', [antreanController::class, 'formVerify']);
Route::get('/kirim-ulang-otp', [antreanController::class, 'kirimUlangOtp']);

Route::put('/ambil-antrean/{antrean}', [antreanController::class, 'ambilAntrean']);
Route::put('/action/ubahJamSidang/{antrean}', [pengajuan_jam_sidangController::class, 'editJamSidang']);

Route::get("/sendsms", [SmsController::class, 'sendSms']);

// Route::post('/store/buatAntrean', [antreanController::class, 'store']);
Route::post('/login/action', [antreanController::class, 'login']);
Route::post('/login/verify-otp', [antreanController::class, 'verifyOtp']);
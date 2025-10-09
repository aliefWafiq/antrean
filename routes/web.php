<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\antreanController;
use App\Http\Controllers\SmsController;

Route::get( '/login', [antreanController::class, 'loginView']);
Route::get('/', [antreanController::class, 'home']);
Route::get('/antrean', [antreanController::class, 'antrean'])->name('antrean')->middleware('auth');
Route::get('/ubahJamSidang', [antreanController::class, 'ubahJamSidang'])->name('ubahJamSidang')->middleware('auth');
Route::get('/ambil-antrean', [antreanController::class, 'ambilAntrean']);

Route::get("/sendsms", [SmsController::class, 'sendSms']);

Route::post('/store/buatAntrean', [antreanController::class, 'store']);
Route::post('/login/action', [antreanController::class, 'login']);

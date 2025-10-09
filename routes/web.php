<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\antreanController;
use App\Http\Controllers\SmsController;

Route::get('/', [antreanController::class, 'home']);
Route::get('/antrean', [antreanController::class, 'antrean']);
Route::get('/ambil-antrean', [antreanController::class, 'ambilAntrean']);
Route::get( '/login', [antreanController::class, 'loginView']);
Route::get('/ubahJamSidang', [antreanController::class, 'ubahJamSidang']);

Route::get("/sendsms", [SmsController::class, 'sendSms']);

Route::post('/store/buatAntrean', [antreanController::class, 'store']);
Route::post('/login/action', [antreanController::class, 'login']);

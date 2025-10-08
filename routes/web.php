<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\antreanController;
use App\Http\Controllers\SmsController;

Route::get('/', [antreanController::class, 'home']);
Route::get('/antrean/{id}', [antreanController::class, 'antrean']);
Route::get('/ambil-antrean', [antreanController::class, 'ambilAntrean']);
Route::get( '/login', [antreanController::class, 'login']);
Route::get('/ubahJamSidang', [antreanController::class, 'ubahJamSidang']);

Route::get("/sendsms", [SmsController::class, 'sendSms']);

Route::post('/store/buatAntrean', [antreanController::class, 'store']);

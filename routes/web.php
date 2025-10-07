<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\antreanController;

Route::get('/', [antreanController::class, 'home']);
Route::get('/antrean/{id}', [antreanController::class, 'antrean']);
Route::get('/ambil-antrean', [antreanController::class, 'ambilAntrean']);
Route::get( '/login', [antreanController::class, 'login']);
Route::get('/ubahJamSidang', [antreanController::class, 'ubahJamSidang']);

Route::post('/store/buatAntrean', [antreanController::class, 'store']);

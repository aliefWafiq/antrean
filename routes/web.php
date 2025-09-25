<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\antreanController;

Route::get('/', [antreanController::class, 'home']);
Route::get('/ambil-antrean', [antreanController::class, 'ambilAntrean']);

Route::post('/store/buatAntrean', [antreanController::class, 'store']);

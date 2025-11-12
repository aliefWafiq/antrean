<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\antreanController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\pengajuan_jam_sidangController;

Route::get( '/login', [antreanController::class, 'loginView'])->name('login');
Route::get('/', [antreanController::class, 'home']);
Route::get('/antrean', [antreanController::class, 'antrean'])->name('antrean')->middleware('perkara.auth');
Route::get('/requestJamSidang', [pengajuan_jam_sidangController::class, 'ubahJamSidang'])->name('ubahJamSidang')->middleware('perkara.auth');
Route::get('/search/{query}', [antreanController::class, 'search']);
Route::get('/logout', [antreanController::class, 'logout']);

Route::put('/action/requestJamSidang/{antrean}', [pengajuan_jam_sidangController::class, 'editJamSidang']);

Route::post('/login/action', [antreanController::class, 'login']);
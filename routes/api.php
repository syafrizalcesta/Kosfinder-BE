<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KosController; // <-- 1. Tambahkan import ini di bagian atas

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/kos', [KosController::class, 'index']);

Route::get('/kos/{id}', [KosController::class, 'show']);
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rute untuk Login & Logout
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute yang hanya bisa diakses kalau sudah login (Middleware 'auth')
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return "Berhasil Login! Ini halaman Dashboard Gudang. <br> <form action='/logout' method='POST'>".csrf_field()."<button type='submit'>Logout</button></form>";
    });
});
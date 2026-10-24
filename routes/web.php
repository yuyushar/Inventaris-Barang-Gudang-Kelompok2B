<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rute untuk Login & Logout
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        if (auth()->user()->role === 'admin') {
            return redirect('/admin/dashboard');
        }
        return redirect('/petugas/dashboard');
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        });

    });

    Route::middleware('role:petugas')->prefix('petugas')->group(function () {

        Route::get('/dashboard', function () {
            return view('petugas.dashboard');
        });

    });
});
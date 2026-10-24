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
        Route::get('/dashboard', function () { return view('admin.dashboard'); });
        
        // Halaman Kosongan Admin
        Route::get('/user', function () { return view('admin.user'); });
        Route::get('/supplier', function () { return view('admin.supplier'); });
        Route::get('/kategori', function () { return view('admin.kategori'); });
        Route::get('/barang', function () { return view('admin.barang'); });
        Route::get('/laporan', function () { return view('admin.laporan'); });
    });
    Route::middleware('role:petugas')->prefix('petugas')->group(function () {
        Route::get('/dashboard', function () { return view('petugas.dashboard'); });
        
        // Halaman Kosongan Petugas
        Route::get('/barang-masuk', function () { return view('petugas.barang_masuk'); });
        Route::get('/barang-keluar', function () { return view('petugas.barang_keluar'); });
        Route::get('/peminjaman', function () { return view('petugas.peminjaman'); });
        Route::get('/riwayat-stok', function () { return view('petugas.riwayat_stok'); });
    });
});
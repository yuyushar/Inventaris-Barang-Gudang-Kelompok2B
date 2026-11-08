<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BarangController;
// --------------------------------------------------------------------------
// 1. RUTE PUBLIK & GUEST (Bisa diakses tanpa login)
// --------------------------------------------------------------------------

// Halaman Panduan (Publik: Tamu, Admin, & Petugas bisa akses kapan saja)
Route::get('/panduan', function () {
    return view('panduan');
});

// Fitur Login (Hanya untuk yang BELUM login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout (Hanya untuk yang SUDAH login)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');


// --------------------------------------------------------------------------
// 2. RUTE TERPROTEKSI (Wajib Login / Auth)
// --------------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Jalur Utama (/): Mengarahkan otomatis sesuai Role
    Route::get('/', function () {
        if (auth()->user()->role === 'admin') {
            return redirect('/admin/dashboard');
        }
        return redirect('/petugas/dashboard');
    });

    // Area Admin (Khusus Admin)
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', function () { return view('admin.dashboard'); });
        Route::resource('/user', UserController::class)->except(['create', 'edit', 'show']);
        Route::resource('/supplier', SupplierController::class)->except(['create', 'edit', 'show']);
        Route::resource('/kategori', KategoriController::class)->except(['create', 'edit', 'show']);
        Route::resource('/barang', BarangController::class)->except(['create', 'edit', 'show']);
        Route::get('/laporan', function () { return view('admin.laporan'); });
    });

    // Area Petugas (Khusus Petugas)
    Route::middleware('role:petugas')->prefix('petugas')->group(function () {
        Route::get('/dashboard', function () { return view('petugas.dashboard'); });
        
        Route::get('/barang-masuk', function () { return view('petugas.barang_masuk'); });
        Route::get('/barang-keluar', function () { return view('petugas.barang_keluar'); });
        Route::get('/peminjaman', function () { return view('petugas.peminjaman'); });
        Route::get('/riwayat-stok', function () { return view('petugas.riwayat_stok'); });
    });
});
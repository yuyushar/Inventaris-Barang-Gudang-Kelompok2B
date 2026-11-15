<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\RiwayatStokController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/riwayat-stok', [RiwayatStokController::class, 'index'])->name('riwayat-stok.index');

    // Jalur Utama (/): Mengarahkan otomatis sesuai Role
    Route::get('/', function () {
        if (auth()->user()->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        return redirect('/petugas/dashboard');
    });

    // Area Admin (Khusus Admin)
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        });
        Route::resource('/user', UserController::class)->except(['create', 'edit', 'show']);
        Route::resource('/supplier', SupplierController::class)->except(['create', 'edit', 'show']);
        Route::resource('/kategori', KategoriController::class)->except(['create', 'edit', 'show']);
        Route::resource('/barang', BarangController::class)->except(['create', 'edit', 'show']);
        Route::get('/laporan', function () {
            return view('admin.laporan');
        });
    });

    // Area Petugas (Khusus Petugas)
    Route::middleware('role:petugas')->prefix('petugas')->group(function () {
        Route::get('/dashboard', function () {
            return view('petugas.dashboard');
        });

        Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('petugas.barang-masuk.index');
        Route::post('/barang-masuk', [BarangMasukController::class, 'store'])->name('petugas.barang-masuk.store');
        Route::get('/barang-keluar', [BarangKeluarController::class, 'index'])->name('petugas.barang-keluar.index');
        Route::post('/barang-keluar', [BarangKeluarController::class, 'store'])->name('petugas.barang-keluar.store');
        Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('petugas.peminjaman.index');
        Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('petugas.peminjaman.store');
        Route::patch('/peminjaman/{peminjaman}/pengembalian', [PeminjamanController::class, 'return'])
            ->name('petugas.peminjaman.return');
        Route::get('/riwayat-stok', [RiwayatStokController::class, 'index'])
            ->name('petugas.riwayat-stok.index');
    });
});

<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\Barang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. User Default (Admin & Petugas)
        User::firstOrCreate(
            ['email' => 'admin@gudang.com'],
            [
                'name' => 'Admin Utama',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'petugas@gudang.com'],
            [
                'name' => 'Petugas Gudang',
                'password' => Hash::make('password'),
                'role' => 'petugas',
            ]
        );

        // 2. Kategori Sample
        $kategoriElektronik = Kategori::firstOrCreate(
            ['nama_kategori' => 'Elektronik'],
            ['deskripsi' => 'Perangkat keras & komponen listrik']
        );

        $kategoriJaringan = Kategori::firstOrCreate(
            ['nama_kategori' => 'Jaringan'],
            ['deskripsi' => 'Perkakas & perangkat networking']
        );

        $kategoriATK = Kategori::firstOrCreate(
            ['nama_kategori' => 'ATK'],
            ['deskripsi' => 'Alat tulis kantor']
        );

        // 3. Supplier Sample
        Supplier::firstOrCreate(
            ['nama_supplier' => 'PT Tekno Utama'],
            [
                'telepon' => '081234567890',
                'email' => 'supplier@tekno.com',
                'alamat' => 'Jl. Slamet Riyadi No. 12, Surakarta',
            ]
        );

        // 4. Data Induk Barang Sample
        Barang::firstOrCreate(
            ['kode_barang' => 'BRG-001'],
            [
                'nama_barang' => 'Crimping Tool RJ45',
                'kategori_id' => $kategoriJaringan->id,
                'satuan' => 'unit',
                'stok_baik' => 10,
                'stok_rusak_ringan' => 0,
                'stok_rusak' => 0,
                'stok_dipinjam' => 0,
                'stok_minimum' => 2,
                'keterangan' => 'Tang krimping kabel LAN UTP',
            ]
        );

        Barang::firstOrCreate(
            ['kode_barang' => 'BRG-002'],
            [
                'nama_barang' => 'MikroTik RB951Ui-2HnD',
                'kategori_id' => $kategoriJaringan->id,
                'satuan' => 'unit',
                'stok_baik' => 5,
                'stok_rusak_ringan' => 0,
                'stok_rusak' => 0,
                'stok_dipinjam' => 0,
                'stok_minimum' => 1,
                'keterangan' => 'Routerboard Wi-Fi lab',
            ]
        );

        Barang::firstOrCreate(
            ['kode_barang' => 'BRG-003'],
            [
                'nama_barang' => 'Multimeter Digital',
                'kategori_id' => $kategoriElektronik->id,
                'satuan' => 'unit',
                'stok_baik' => 8,
                'stok_rusak_ringan' => 0,
                'stok_rusak' => 0,
                'stok_dipinjam' => 0,
                'stok_minimum' => 2,
                'keterangan' => 'Alat ukur tegangan & hambatan',
            ]
        );
    }
}
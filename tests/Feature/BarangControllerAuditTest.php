<?php

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not allow inventory creation to set stock outside transaction logs', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $kategori = Kategori::query()->create(['nama_kategori' => 'Aksesoris']);

    $response = $this->actingAs($admin)->post(route('barang.store'), [
        'kode_barang' => 'AUD-STOCK-001',
        'nama_barang' => 'Headset',
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
        'stok_minimum' => 1,
        'stok_baik' => 100,
        'stok_rusak_ringan' => 50,
    ]);

    $response->assertRedirect('/admin/barang');
    $this->assertDatabaseHas('barang', [
        'kode_barang' => 'AUD-STOCK-001',
        'stok_baik' => 0,
        'stok_rusak_ringan' => 0,
    ]);
});

it('does not allow inventory updates to change stock without a transaction log', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $kategori = Kategori::query()->create(['nama_kategori' => 'Perangkat']);
    $barang = Barang::query()->create([
        'kode_barang' => 'AUD-STOCK-002',
        'nama_barang' => 'Printer',
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
        'stok_baik' => 5,
    ]);

    $response = $this->actingAs($admin)->put(route('barang.update', $barang), [
        'kode_barang' => 'AUD-STOCK-002',
        'nama_barang' => 'Printer Baru',
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
        'stok_minimum' => 2,
        'stok_baik' => 100,
    ]);

    $response->assertRedirect('/admin/barang');
    $this->assertDatabaseHas('barang', [
        'id' => $barang->id,
        'nama_barang' => 'Printer Baru',
        'stok_baik' => 5,
        'stok_minimum' => 2,
    ]);
    $this->assertDatabaseCount('riwayat_stok', 0);
});

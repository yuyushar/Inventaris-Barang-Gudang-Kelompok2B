<?php

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('adds good stock and records an incoming transaction and stock log', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $kategori = Kategori::query()->create(['nama_kategori' => 'Elektronik']);
    $supplier = Supplier::query()->create(['nama_supplier' => 'Supplier A']);
    $barang = Barang::query()->create([
        'kode_barang' => 'BRG-001',
        'nama_barang' => 'Keyboard',
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
        'stok_baik' => 3,
    ]);

    $response = $this->actingAs($petugas)->post(route('petugas.barang-masuk.store'), [
        'supplier_id' => $supplier->id,
        'barang_id' => $barang->id,
        'jumlah' => 4,
        'tanggal' => '2026-11-14 08:00:00',
    ]);

    $response->assertRedirect(route('petugas.barang-masuk.index'));
    $this->assertDatabaseHas('barang', ['id' => $barang->id, 'stok_baik' => 7]);
    $this->assertDatabaseHas('barang_masuk', [
        'barang_id' => $barang->id,
        'supplier_id' => $supplier->id,
        'jumlah' => 4,
        'user_id' => $petugas->id,
    ]);
    $this->assertDatabaseHas('riwayat_stok', [
        'barang_id' => $barang->id,
        'user_id' => $petugas->id,
        'jenis_transaksi' => 'masuk',
        'kondisi' => 'baik',
        'jumlah' => 4,
    ]);
});

it('subtracts good stock and records an outgoing transaction and stock log', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $kategori = Kategori::query()->create(['nama_kategori' => 'Elektronik']);
    $barang = Barang::query()->create([
        'kode_barang' => 'BRG-002',
        'nama_barang' => 'Mouse',
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
        'stok_baik' => 8,
    ]);

    $response = $this->actingAs($petugas)->post(route('petugas.barang-keluar.store'), [
        'barang_id' => $barang->id,
        'jumlah' => 3,
        'tujuan' => 'Ruang administrasi',
        'tanggal' => '2026-11-14 09:00:00',
    ]);

    $response->assertRedirect(route('petugas.barang-keluar.index'));
    $this->assertDatabaseHas('barang', ['id' => $barang->id, 'stok_baik' => 5]);
    $this->assertDatabaseHas('barang_keluar', [
        'barang_id' => $barang->id,
        'jumlah' => 3,
        'tujuan' => 'Ruang administrasi',
        'user_id' => $petugas->id,
    ]);
    $this->assertDatabaseHas('riwayat_stok', [
        'barang_id' => $barang->id,
        'user_id' => $petugas->id,
        'jenis_transaksi' => 'keluar',
        'kondisi' => 'baik',
        'jumlah' => 3,
    ]);
});

it('rejects an outgoing transaction when good stock is insufficient', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $kategori = Kategori::query()->create(['nama_kategori' => 'Elektronik']);
    $barang = Barang::query()->create([
        'kode_barang' => 'BRG-003',
        'nama_barang' => 'Monitor',
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
        'stok_baik' => 2,
    ]);

    $response = $this->actingAs($petugas)->post(route('petugas.barang-keluar.store'), [
        'barang_id' => $barang->id,
        'jumlah' => 3,
        'tujuan' => 'Ruang administrasi',
        'tanggal' => '2026-11-14 10:00:00',
    ]);

    $response->assertSessionHasErrors('jumlah');
    $this->assertDatabaseHas('barang', ['id' => $barang->id, 'stok_baik' => 2]);
    $this->assertDatabaseCount('barang_keluar', 0);
    $this->assertDatabaseCount('riwayat_stok', 0);
});

it('forbids an admin from accessing the transaction pages', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('petugas.barang-masuk.index'))
        ->assertForbidden();
});

it('renders the incoming transaction log for a staff user', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);

    $this->actingAs($petugas)
        ->get(route('petugas.barang-masuk.index'))
        ->assertOk()
        ->assertSeeText('Log Barang Masuk');
});

it('renders the outgoing transaction log for a staff user', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);

    $this->actingAs($petugas)
        ->get(route('petugas.barang-keluar.index'))
        ->assertOk()
        ->assertSeeText('Log Barang Keluar');
});

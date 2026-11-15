<?php

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('moves available good stock to borrowed stock when a loan is recorded', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $kategori = Kategori::query()->create(['nama_kategori' => 'Elektronik']);
    $barang = Barang::query()->create([
        'kode_barang' => 'PIN-001',
        'nama_barang' => 'Laptop',
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
        'stok_baik' => 6,
        'stok_dipinjam' => 0,
    ]);

    $response = $this->actingAs($petugas)->post(route('petugas.peminjaman.store'), [
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Ayu',
        'jumlah' => 2,
    ]);

    $response->assertRedirect(route('petugas.peminjaman.index'));
    $this->assertDatabaseHas('barang', [
        'id' => $barang->id,
        'stok_baik' => 4,
        'stok_dipinjam' => 2,
    ]);
    $this->assertDatabaseHas('peminjaman', [
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Ayu',
        'jumlah' => 2,
        'kondisi_saat_dipinjam' => 'baik',
        'status' => 'dipinjam',
        'user_id' => $petugas->id,
    ]);
    $this->assertDatabaseHas('riwayat_stok', [
        'barang_id' => $barang->id,
        'jenis_transaksi' => 'peminjaman',
        'kondisi' => 'baik',
        'jumlah' => 2,
        'user_id' => $petugas->id,
    ]);
});

it('rejects a loan when available good stock is insufficient', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $kategori = Kategori::query()->create(['nama_kategori' => 'Elektronik']);
    $barang = Barang::query()->create([
        'kode_barang' => 'PIN-002',
        'nama_barang' => 'Proyektor',
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
        'stok_baik' => 1,
        'stok_dipinjam' => 0,
    ]);

    $response = $this->actingAs($petugas)->post(route('petugas.peminjaman.store'), [
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Budi',
        'jumlah' => 2,
    ]);

    $response->assertSessionHasErrors('jumlah');
    $this->assertDatabaseHas('barang', [
        'id' => $barang->id,
        'stok_baik' => 1,
        'stok_dipinjam' => 0,
    ]);
    $this->assertDatabaseCount('peminjaman', 0);
    $this->assertDatabaseCount('riwayat_stok', 0);
});

it('moves a returned loan into the selected condition stock', function (string $condition, string $stockColumn) {
    $petugasPinjam = User::factory()->create(['role' => 'petugas']);
    $petugasKembali = User::factory()->create(['role' => 'petugas']);
    $kategori = Kategori::query()->create(['nama_kategori' => 'Elektronik']);
    $barang = Barang::query()->create([
        'kode_barang' => 'PIN-003',
        'nama_barang' => 'Tablet',
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
        'stok_baik' => 0,
        'stok_dipinjam' => 2,
    ]);
    $peminjaman = Peminjaman::query()->create([
        'nomor_peminjaman' => 'PM-TEST-001',
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Citra',
        'jumlah' => 2,
        'tanggal_pinjam' => now()->subDay(),
        'kondisi_saat_dipinjam' => 'baik',
        'status' => 'dipinjam',
        'user_id' => $petugasPinjam->id,
    ]);

    $response = $this->actingAs($petugasKembali)->patch(
        route('petugas.peminjaman.return', $peminjaman),
        ['kondisi_saat_dikembalikan' => $condition],
    );

    $response->assertRedirect(route('petugas.peminjaman.index'));
    $this->assertDatabaseHas('barang', [
        'id' => $barang->id,
        'stok_dipinjam' => 0,
        $stockColumn => 2,
    ]);
    $this->assertDatabaseHas('peminjaman', [
        'id' => $peminjaman->id,
        'status' => 'dikembalikan',
        'kondisi_saat_dikembalikan' => $condition,
        'user_pengembali_id' => $petugasKembali->id,
    ]);
    $this->assertDatabaseHas('riwayat_stok', [
        'barang_id' => $barang->id,
        'jenis_transaksi' => 'pengembalian',
        'kondisi' => $condition,
        'jumlah' => 2,
        'user_id' => $petugasKembali->id,
    ]);
})->with([
    'good condition' => ['baik', 'stok_baik'],
    'light damage' => ['rusak_ringan', 'stok_rusak_ringan'],
    'damaged' => ['rusak', 'stok_rusak'],
]);

it('does not return a loan more than once', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $kategori = Kategori::query()->create(['nama_kategori' => 'Elektronik']);
    $barang = Barang::query()->create([
        'kode_barang' => 'PIN-004',
        'nama_barang' => 'Kamera',
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
        'stok_baik' => 1,
        'stok_dipinjam' => 0,
    ]);
    $peminjaman = Peminjaman::query()->create([
        'nomor_peminjaman' => 'PM-TEST-002',
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Dina',
        'jumlah' => 1,
        'tanggal_pinjam' => now()->subDay(),
        'tanggal_kembali' => now(),
        'kondisi_saat_dipinjam' => 'baik',
        'kondisi_saat_dikembalikan' => 'baik',
        'status' => 'dikembalikan',
        'user_id' => $petugas->id,
        'user_pengembali_id' => $petugas->id,
    ]);

    $response = $this->actingAs($petugas)->patch(
        route('petugas.peminjaman.return', $peminjaman),
        ['kondisi_saat_dikembalikan' => 'baik'],
    );

    $response->assertSessionHasErrors('peminjaman');
    $this->assertDatabaseHas('barang', [
        'id' => $barang->id,
        'stok_baik' => 1,
        'stok_dipinjam' => 0,
    ]);
    $this->assertDatabaseCount('riwayat_stok', 0);
});

it('keeps a loan active when borrowed stock is inconsistent during return', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $kategori = Kategori::query()->create(['nama_kategori' => 'Elektronik']);
    $barang = Barang::query()->create([
        'kode_barang' => 'PIN-005',
        'nama_barang' => 'Speaker',
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
        'stok_baik' => 0,
        'stok_dipinjam' => 0,
    ]);
    $peminjaman = Peminjaman::query()->create([
        'nomor_peminjaman' => 'PM-TEST-003',
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Eka',
        'jumlah' => 1,
        'tanggal_pinjam' => now()->subDay(),
        'kondisi_saat_dipinjam' => 'baik',
        'status' => 'dipinjam',
        'user_id' => $petugas->id,
    ]);

    $response = $this->actingAs($petugas)->patch(
        route('petugas.peminjaman.return', $peminjaman),
        ['kondisi_saat_dikembalikan' => 'baik'],
    );

    $response->assertSessionHasErrors('jumlah');
    $this->assertDatabaseHas('peminjaman', [
        'id' => $peminjaman->id,
        'status' => 'dipinjam',
        'tanggal_kembali' => null,
    ]);
    $this->assertDatabaseHas('barang', [
        'id' => $barang->id,
        'stok_baik' => 0,
        'stok_dipinjam' => 0,
    ]);
    $this->assertDatabaseCount('riwayat_stok', 0);
});

it('forbids an admin from accessing the loan module', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('petugas.peminjaman.index'))
        ->assertForbidden();
});

it('renders the loan and return log for a staff user', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);

    $this->actingAs($petugas)
        ->get(route('petugas.peminjaman.index'))
        ->assertOk()
        ->assertSeeText('Log Peminjaman dan Pengembalian');
});

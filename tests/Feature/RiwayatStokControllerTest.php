<?php

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\RiwayatStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAuditLog(Barang $barang, User $user, array $attributes = []): RiwayatStok
{
    return RiwayatStok::query()->create(array_merge([
        'barang_id' => $barang->id,
        'user_id' => $user->id,
        'jenis_transaksi' => 'masuk',
        'kondisi' => 'baik',
        'jumlah' => 3,
        'tanggal' => '2026-11-15 10:30:00',
        'keterangan' => 'Nomor transaksi: BM-AUDIT-001',
    ], $attributes));
}

function createAuditItem(string $code, string $name): Barang
{
    $kategori = Kategori::query()->create(['nama_kategori' => 'Perlengkapan '.$code]);

    return Barang::query()->create([
        'kode_barang' => $code,
        'nama_barang' => $name,
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
    ]);
}

it('shows inventory audit details with item, actor, date, and transaction reference', function () {
    $petugas = User::factory()->create(['role' => 'petugas', 'name' => 'Petugas Satu']);
    $barang = createAuditItem('AUD-001', 'Keyboard');
    createAuditLog($barang, $petugas);

    $this->actingAs($petugas)
        ->get(route('riwayat-stok.index'))
        ->assertOk()
        ->assertSeeText('Riwayat Stok')
        ->assertSeeText('Barang masuk')
        ->assertSeeText('AUD-001')
        ->assertSeeText('Keyboard')
        ->assertSeeText('Petugas Satu')
        ->assertSeeText('BM-AUDIT-001');
});

it('filters audit logs by transaction, item, actor, date range, and search text', function () {
    $petugasSatu = User::factory()->create(['role' => 'petugas', 'name' => 'Petugas Satu']);
    $petugasDua = User::factory()->create(['role' => 'petugas', 'name' => 'Petugas Dua']);
    $barangSatu = createAuditItem('AUD-002', 'Mouse');
    $barangDua = createAuditItem('AUD-003', 'Monitor');
    $matchingLog = createAuditLog($barangSatu, $petugasSatu, [
        'tanggal' => '2026-11-15 12:00:00',
        'keterangan' => 'Nomor transaksi: BK-AUDIT-001',
        'jenis_transaksi' => 'keluar',
    ]);
    createAuditLog($barangDua, $petugasDua, [
        'tanggal' => '2026-11-14 12:00:00',
        'keterangan' => 'Nomor transaksi: BM-AUDIT-002',
    ]);

    $response = $this->actingAs($petugasSatu)
        ->get(route('riwayat-stok.index', [
            'jenis_transaksi' => 'keluar',
            'barang_id' => $barangSatu->id,
            'user_id' => $petugasSatu->id,
            'tanggal_dari' => '2026-11-15',
            'tanggal_sampai' => '2026-11-15',
            'cari' => 'BK-AUDIT-001',
        ]));

    $response
        ->assertOk()
        ->assertSeeText('BK-AUDIT-001')
        ->assertDontSeeText('BM-AUDIT-002');

    expect($response->viewData('riwayat')->getCollection()->modelKeys())
        ->toBe([$matchingLog->id]);
});

it('rejects a date range whose end is before its start', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);

    $this->actingAs($petugas)
        ->get(route('riwayat-stok.index', [
            'tanggal_dari' => '2026-11-16',
            'tanggal_sampai' => '2026-11-15',
        ]))
        ->assertSessionHasErrors('tanggal_sampai');
});

it('allows administrators and staff to view the audit trail', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get(route('riwayat-stok.index'))
        ->assertOk()
        ->assertSeeText('Log Audit');
})->with([
    'administrator' => 'admin',
    'staff' => 'petugas',
]);

it('redirects guests to login before showing the audit trail', function () {
    $this->get(route('riwayat-stok.index'))
        ->assertRedirect(route('login'));
});

it('keeps the existing staff audit trail URL available', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);

    $this->actingAs($petugas)
        ->get(route('petugas.riwayat-stok.index'))
        ->assertOk()
        ->assertSeeText('Log Audit');
});

it('does not allow changes through the audit trail endpoint', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);

    $this->actingAs($petugas)
        ->post(route('riwayat-stok.index'))
        ->assertMethodNotAllowed();
});

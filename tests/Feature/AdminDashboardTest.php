<?php

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\RiwayatStok;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createDashboardItem(string $code, string $name, array $attributes = []): Barang
{
    $kategori = Kategori::query()->firstOrCreate(['nama_kategori' => 'Kategori Dashboard']);

    return Barang::query()->create(array_merge([
        'kode_barang' => $code,
        'nama_barang' => $name,
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
    ], $attributes));
}

it('redirects guests to the login page', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('forbids petugas from opening the admin dashboard', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);

    $this->actingAs($petugas)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('shows friendly empty states when the warehouse has no data yet', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Dashboard Statistik')
        ->assertSeeText('Belum ada kategori tercatat.')
        ->assertSeeText('Semua stok masih aman.')
        ->assertSeeText('Tidak ada peminjaman aktif.')
        ->assertSeeText('Belum ada aktivitas stok.');
});

it('summarises stock by condition on the stat cards', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    createDashboardItem('DSB-001', 'Kabel LAN', [
        'stok_baik' => 3,
        'stok_rusak_ringan' => 1,
        'stok_rusak' => 2,
        'stok_dipinjam' => 4,
        'stok_minimum' => 5,
    ]);
    createDashboardItem('DSB-002', 'Router Aman', [
        'stok_baik' => 10,
        'stok_minimum' => 2,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('kartu', fn (array $kartu): bool => $kartu['jenis_barang']['nilai'] === 2
            && $kartu['stok_baik']['nilai'] === 13
            && $kartu['dipinjam']['nilai'] === 4
            && $kartu['rusak']['nilai'] === 3
            && $kartu['menipis']['nilai'] === 1)
        ->assertViewHas('grafik', fn (array $grafik): bool => array_column($grafik['kondisi'], 'nilai') === [13, 1, 2, 4]
            && $grafik['kategori']['labels'] === ['Kategori Dashboard']
            && $grafik['kategori']['data'] === [20]);
});

it('lists only items at or below their minimum stock', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    createDashboardItem('DSB-003', 'Kabel Menipis', ['stok_baik' => 1, 'stok_minimum' => 5]);
    createDashboardItem('DSB-004', 'Router Aman', ['stok_baik' => 20, 'stok_minimum' => 2]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Kabel Menipis')
        ->assertDontSeeText('Router Aman');
});

it('charts only the last seven days of incoming and outgoing items', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $petugas = User::factory()->create(['role' => 'petugas']);
    $supplier = Supplier::query()->create(['nama_supplier' => 'Supplier Dashboard']);
    $barang = createDashboardItem('DSB-005', 'Mouse', ['stok_baik' => 50]);

    $catatMasuk = fn (string $nomor, int $jumlah, $tanggal) => BarangMasuk::query()->create([
        'nomor_transaksi' => $nomor,
        'tanggal' => $tanggal,
        'supplier_id' => $supplier->id,
        'barang_id' => $barang->id,
        'jumlah' => $jumlah,
        'user_id' => $petugas->id,
    ]);

    $catatKeluar = fn (string $nomor, int $jumlah, $tanggal) => BarangKeluar::query()->create([
        'nomor_transaksi' => $nomor,
        'tanggal' => $tanggal,
        'barang_id' => $barang->id,
        'jumlah' => $jumlah,
        'tujuan' => 'Laboratorium',
        'user_id' => $petugas->id,
    ]);

    $catatMasuk('BM-DSB-001', 5, now());
    $catatMasuk('BM-DSB-002', 2, now()->subDays(3));
    $catatMasuk('BM-DSB-003', 99, now()->subDays(10));
    $catatKeluar('BK-DSB-001', 4, now());
    $catatKeluar('BK-DSB-002', 50, now()->subDays(10));

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('grafik', function (array $grafik): bool {
            $transaksi = $grafik['transaksi'];

            return count($transaksi['labels']) === 7
                && $transaksi['labels'][6] === today()->format('d/m')
                && $transaksi['masuk'] === [0, 0, 0, 2, 0, 0, 5]
                && $transaksi['keluar'] === [0, 0, 0, 0, 0, 0, 4];
        })
        ->assertViewHas('kartu', fn (array $kartu): bool => $kartu['masuk_7_hari']['nilai'] === 7
            && $kartu['masuk_7_hari']['keterangan'] === 'Keluar: 4 unit');
});

it('lists active loans and the most recent stock activity', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $petugas = User::factory()->create(['role' => 'petugas', 'name' => 'Petugas Satu']);
    $barang = createDashboardItem('DSB-006', 'Proyektor', ['stok_baik' => 4, 'stok_dipinjam' => 1]);

    $buatPeminjaman = fn (string $nomor, string $peminjam, string $status) => Peminjaman::query()->create([
        'nomor_peminjaman' => $nomor,
        'barang_id' => $barang->id,
        'peminjam_nama' => $peminjam,
        'jumlah' => 1,
        'tanggal_pinjam' => now(),
        'kondisi_saat_dipinjam' => 'baik',
        'status' => $status,
        'user_id' => $petugas->id,
    ]);

    $buatPeminjaman('PJM-DSB-001', 'Budi Santoso', 'dipinjam');
    $buatPeminjaman('PJM-DSB-002', 'Sari Lestari', 'dikembalikan');

    RiwayatStok::query()->create([
        'barang_id' => $barang->id,
        'user_id' => $petugas->id,
        'jenis_transaksi' => 'masuk',
        'kondisi' => 'baik',
        'jumlah' => 3,
        'tanggal' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Budi Santoso')
        ->assertSeeText('PJM-DSB-001')
        ->assertDontSeeText('Sari Lestari')
        ->assertSeeText('Barang masuk')
        ->assertSeeText('Petugas Satu')
        ->assertSeeText('Proyektor');
});

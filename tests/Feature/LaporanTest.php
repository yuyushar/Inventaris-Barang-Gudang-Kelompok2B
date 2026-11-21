<?php

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\RiwayatStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buatBarangLaporan(string $kode, string $nama, string $namaKategori, array $atribut = []): Barang
{
    $kategori = Kategori::query()->firstOrCreate(['nama_kategori' => $namaKategori]);

    return Barang::query()->create(array_merge([
        'kode_barang' => $kode,
        'nama_barang' => $nama,
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
    ], $atribut));
}

function buatLogLaporan(Barang $barang, User $user, string $jenis, int $jumlah, string $tanggal, ?string $keterangan = null): RiwayatStok
{
    return RiwayatStok::query()->create([
        'barang_id' => $barang->id,
        'user_id' => $user->id,
        'jenis_transaksi' => $jenis,
        'kondisi' => 'baik',
        'jumlah' => $jumlah,
        'tanggal' => $tanggal,
        'keterangan' => $keterangan,
    ]);
}

it('redirects guests to the login page', function () {
    $this->get(route('admin.laporan.index'))->assertRedirect(route('login'));
});

it('forbids petugas from every report endpoint', function (string $rute) {
    $petugas = User::factory()->create(['role' => 'petugas']);

    $this->actingAs($petugas)->get(route($rute))->assertForbidden();
})->with([
    'halaman laporan' => 'admin.laporan.index',
    'halaman cetak' => 'admin.laporan.cetak',
    'unduh excel' => 'admin.laporan.export',
]);

it('summarises the current stock position by default', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    buatBarangLaporan('LAP-001', 'Kabel LAN', 'Jaringan', [
        'stok_baik' => 3,
        'stok_rusak_ringan' => 1,
        'stok_rusak' => 2,
        'stok_dipinjam' => 4,
        'stok_minimum' => 5,
    ]);
    buatBarangLaporan('LAP-002', 'Router Aman', 'Jaringan', ['stok_baik' => 10, 'stok_minimum' => 2]);

    $this->actingAs($admin)
        ->get(route('admin.laporan.index'))
        ->assertOk()
        ->assertSeeText('Laporan Stok Barang')
        ->assertSeeText('Kabel LAN')
        ->assertSeeText('Perlu perhatian')
        ->assertViewHas('laporan', fn (array $laporan): bool => $laporan['jenis'] === 'stok'
            && $laporan['total'] === 2
            && collect($laporan['ringkasan'])->pluck('nilai', 'label')->all() === [
                'Jenis barang' => 2,
                'Stok baik' => 13,
                'Rusak' => 3,
                'Sedang dipinjam' => 4,
                'Stok menipis' => 1,
            ]
            && $laporan['baris'][0][9] === 10);
});

it('limits the stock report to the chosen category', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    buatBarangLaporan('LAP-003', 'Kabel Jaringan', 'Jaringan');
    $barangLain = buatBarangLaporan('LAP-004', 'Pulpen Hitam', 'ATK');

    $this->actingAs($admin)
        ->get(route('admin.laporan.index', ['kategori_id' => $barangLain->kategori_id]))
        ->assertOk()
        ->assertSeeText('Pulpen Hitam')
        ->assertDontSeeText('Kabel Jaringan')
        ->assertSeeText('Kategori: ATK');
});

it('builds the movement report only from the chosen period and transaction type', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $petugas = User::factory()->create(['role' => 'petugas', 'name' => 'Petugas Laporan']);
    $barang = buatBarangLaporan('LAP-005', 'Proyektor', 'Elektronik', ['stok_baik' => 9]);

    buatLogLaporan($barang, $petugas, 'masuk', 5, '2026-11-10 09:00:00', 'Nomor transaksi: BM-LAP-001');
    buatLogLaporan($barang, $petugas, 'masuk', 7, '2026-11-12 09:00:00', 'Nomor transaksi: BM-LAP-002');
    buatLogLaporan($barang, $petugas, 'keluar', 2, '2026-11-12 10:00:00', 'Nomor transaksi: BK-LAP-001');
    buatLogLaporan($barang, $petugas, 'masuk', 99, '2026-10-01 09:00:00', 'Nomor transaksi: BM-LAP-LAMA');

    $this->actingAs($admin)
        ->get(route('admin.laporan.index', [
            'jenis_laporan' => 'mutasi',
            'tanggal_dari' => '2026-11-11',
            'tanggal_sampai' => '2026-11-13',
        ]))
        ->assertOk()
        ->assertSeeText('Laporan Mutasi Stok')
        ->assertSeeText('Periode: 11/11/2026 s.d. 13/11/2026')
        ->assertSeeText('BM-LAP-002')
        ->assertSeeText('BK-LAP-001')
        ->assertDontSeeText('BM-LAP-001')
        ->assertDontSeeText('BM-LAP-LAMA')
        ->assertViewHas('laporan', fn (array $laporan): bool => $laporan['total'] === 2
            && collect($laporan['ringkasan'])->pluck('nilai', 'label')->all() === [
                'Total catatan' => 2,
                'Unit masuk' => 7,
                'Unit keluar' => 2,
                'Unit dipinjamkan' => 0,
                'Unit dikembalikan' => 0,
            ]);

    $this->actingAs($admin)
        ->get(route('admin.laporan.index', [
            'jenis_laporan' => 'mutasi',
            'jenis_transaksi' => 'keluar',
        ]))
        ->assertOk()
        ->assertSeeText('BK-LAP-001')
        ->assertDontSeeText('BM-LAP-002')
        ->assertSeeText('Aktivitas: Barang keluar');
});

it('rejects a report period whose end is before its start', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.laporan.index', [
            'jenis_laporan' => 'mutasi',
            'tanggal_dari' => '2026-11-16',
            'tanggal_sampai' => '2026-11-15',
        ]))
        ->assertSessionHasErrors('tanggal_sampai');
});

it('renders a print-ready page with the signer and every row', function () {
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Pencetak']);
    buatBarangLaporan('LAP-006', 'Keyboard', 'Elektronik', ['stok_baik' => 4]);

    $this->actingAs($admin)
        ->get(route('admin.laporan.cetak'))
        ->assertOk()
        ->assertSeeText('Laporan Stok Barang')
        ->assertSeeText('LAP-006')
        ->assertSeeText('Keyboard')
        ->assertSeeText('Admin Pencetak')
        ->assertSee('window.print()', false);
});

it('downloads an Excel friendly CSV file with a header row and the data', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    buatBarangLaporan('LAP-007', 'Mouse Wireless', 'Elektronik', ['stok_baik' => 6]);

    $response = $this->actingAs($admin)->get(route('admin.laporan.export'));

    $response->assertOk();

    expect($response->headers->get('content-type'))->toContain('text/csv')
        ->and($response->headers->get('content-disposition'))->toContain('laporan-stok-'.today()->toDateString().'.csv');

    $isi = $response->streamedContent();

    expect($isi)->toStartWith("\xEF\xBB\xBF")
        ->toContain('sep=;')
        ->toContain('No;"Kode barang";"Nama barang"')
        ->toContain('LAP-007')
        ->toContain('"Mouse Wireless"');
});

it('neutralises spreadsheet formulas in exported cells', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    buatBarangLaporan('LAP-008', '=HYPERLINK("http://contoh.test")', 'Elektronik');

    $isi = $this->actingAs($admin)->get(route('admin.laporan.export'))->streamedContent();

    expect($isi)->toContain("'=HYPERLINK")
        ->not->toContain(';"=HYPERLINK');
});

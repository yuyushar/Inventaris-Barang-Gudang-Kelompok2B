<?php

/*
 * FE 2 (Estu): tes untuk form transaksi petugas
 * - No. 19 multi-item barang masuk
 * - No. 20 form persetujuan barang keluar
 * - No. 21 tanggal pinjam
 * - No. 22 upload foto bukti pinjam/kembali
 * - No. 23 validasi foto bukti (format & ukuran)
 * Tes lama di BarangMasukKeluarTest & PeminjamanTest tetap berlaku (alur satu barang & tanpa foto).
 */

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

if (! function_exists('fotoBuktiPalsu')) {
    /** PNG 1x1 piksel; dipakai agar tes upload tidak bergantung pada ekstensi GD. */
    function fotoBuktiPalsu(string $nama = 'bukti.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $nama,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='),
        );
    }
}

if (! function_exists('barangUji')) {
    function barangUji(string $kode, int $stokBaik = 0, int $stokDipinjam = 0): Barang
    {
        $kategori = Kategori::query()->firstOrCreate(['nama_kategori' => 'Elektronik']);

        return Barang::query()->create([
            'kode_barang' => $kode,
            'nama_barang' => 'Barang '.$kode,
            'kategori_id' => $kategori->id,
            'satuan' => 'unit',
            'stok_baik' => $stokBaik,
            'stok_dipinjam' => $stokDipinjam,
            'stok_minimum' => 2,
        ]);
    }
}

it('stores several incoming items from one multi-item submission', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $supplier = Supplier::query()->create(['nama_supplier' => 'Supplier Multi']);
    $keyboard = barangUji('MI-001', 3);
    $mouse = barangUji('MI-002', 0);

    $response = $this->actingAs($petugas)->post(route('petugas.barang-masuk.store'), [
        'supplier_id' => $supplier->id,
        'tanggal' => '2026-11-14 08:00:00',
        'items' => [
            ['barang_id' => $keyboard->id, 'jumlah' => 4],
            ['barang_id' => $mouse->id, 'jumlah' => 10],
        ],
    ]);

    $response->assertRedirect(route('petugas.barang-masuk.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('barang', ['id' => $keyboard->id, 'stok_baik' => 7]);
    $this->assertDatabaseHas('barang', ['id' => $mouse->id, 'stok_baik' => 10]);
    $this->assertDatabaseCount('barang_masuk', 2);
    $this->assertDatabaseHas('barang_masuk', [
        'barang_id' => $mouse->id,
        'supplier_id' => $supplier->id,
        'jumlah' => 10,
        'user_id' => $petugas->id,
    ]);
    $this->assertDatabaseCount('riwayat_stok', 2);
    $this->assertDatabaseHas('riwayat_stok', [
        'barang_id' => $keyboard->id,
        'jenis_transaksi' => 'masuk',
        'kondisi' => 'baik',
        'jumlah' => 4,
    ]);
});

it('rejects a multi-item submission that picks the same item twice', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $supplier = Supplier::query()->create(['nama_supplier' => 'Supplier Ganda']);
    $barang = barangUji('MI-003', 1);

    $response = $this->actingAs($petugas)->post(route('petugas.barang-masuk.store'), [
        'supplier_id' => $supplier->id,
        'tanggal' => '2026-11-14 08:00:00',
        'items' => [
            ['barang_id' => $barang->id, 'jumlah' => 2],
            ['barang_id' => $barang->id, 'jumlah' => 3],
        ],
    ]);

    $response->assertSessionHasErrors(['items.0.barang_id', 'items.1.barang_id']);
    $this->assertDatabaseHas('barang', ['id' => $barang->id, 'stok_baik' => 1]);
    $this->assertDatabaseCount('barang_masuk', 0);
    $this->assertDatabaseCount('riwayat_stok', 0);
});

it('rejects a multi-item submission with an invalid quantity row', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $supplier = Supplier::query()->create(['nama_supplier' => 'Supplier Nol']);
    $pertama = barangUji('MI-004', 1);
    $kedua = barangUji('MI-005', 1);

    $response = $this->actingAs($petugas)->post(route('petugas.barang-masuk.store'), [
        'supplier_id' => $supplier->id,
        'tanggal' => '2026-11-14 08:00:00',
        'items' => [
            ['barang_id' => $pertama->id, 'jumlah' => 5],
            ['barang_id' => $kedua->id, 'jumlah' => 0],
        ],
    ]);

    $response->assertSessionHasErrors('items.1.jumlah');
    $this->assertDatabaseHas('barang', ['id' => $pertama->id, 'stok_baik' => 1]);
    $this->assertDatabaseCount('barang_masuk', 0);
});

it('renders the multi-item incoming form with stock data for each item', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    Supplier::query()->create(['nama_supplier' => 'Supplier Form']);
    barangUji('MI-006', 9);

    $this->actingAs($petugas)
        ->get(route('petugas.barang-masuk.index'))
        ->assertOk()
        ->assertSee('name="items[0][barang_id]"', false)
        ->assertSee('name="items[0][jumlah]"', false)
        ->assertSee('data-tx-item-template', false)
        ->assertSee('data-stok="9"', false)
        ->assertSeeText('Form transaksi barang masuk');
});

it('renders the outgoing form with a stock check panel and approval dialog', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    barangUji('BK-UJI-1', 5);

    $this->actingAs($petugas)
        ->get(route('petugas.barang-keluar.index'))
        ->assertOk()
        ->assertSee('data-minimum="2"', false)
        ->assertSee('id="txApprovalModal"', false)
        ->assertSeeText('Setujui pengeluaran stok?')
        ->assertSeeText('CEK KETERSEDIAAN STOK');
});

it('records a loan with a chosen loan date and a proof photo', function () {
    Storage::fake('public');
    $petugas = User::factory()->create(['role' => 'petugas']);
    $barang = barangUji('PJ-001', 5);
    $waktuPinjam = now()->subHour()->startOfMinute();

    $response = $this->actingAs($petugas)->post(route('petugas.peminjaman.store'), [
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Fajar',
        'jumlah' => 2,
        'tanggal_pinjam' => $waktuPinjam->format('Y-m-d H:i:s'),
        'gambar_bukti_pinjam' => fotoBuktiPalsu(),
    ]);

    $response->assertRedirect(route('petugas.peminjaman.index'));
    $peminjaman = Peminjaman::query()->sole();
    expect($peminjaman->gambar_bukti_pinjam)->not->toBeNull()
        ->and($peminjaman->tanggal_pinjam->format('Y-m-d H:i'))->toBe($waktuPinjam->format('Y-m-d H:i'));
    Storage::disk('public')->assertExists($peminjaman->gambar_bukti_pinjam);
    $this->assertDatabaseHas('barang', ['id' => $barang->id, 'stok_baik' => 3, 'stok_dipinjam' => 2]);
});

it('rejects a loan date in the future', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $barang = barangUji('PJ-002', 5);

    $response = $this->actingAs($petugas)->post(route('petugas.peminjaman.store'), [
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Gita',
        'jumlah' => 1,
        'tanggal_pinjam' => now()->addDay()->format('Y-m-d H:i:00'),
    ]);

    $response->assertSessionHasErrors('tanggal_pinjam');
    $this->assertDatabaseCount('peminjaman', 0);
    $this->assertDatabaseHas('barang', ['id' => $barang->id, 'stok_baik' => 5]);
});

it('rejects a loan proof that is not an image', function () {
    Storage::fake('public');
    $petugas = User::factory()->create(['role' => 'petugas']);
    $barang = barangUji('PJ-003', 5);

    $response = $this->actingAs($petugas)->post(route('petugas.peminjaman.store'), [
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Hana',
        'jumlah' => 1,
        'gambar_bukti_pinjam' => UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('gambar_bukti_pinjam');
    $this->assertDatabaseCount('peminjaman', 0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('rejects a loan proof larger than 2 MB', function () {
    Storage::fake('public');
    $petugas = User::factory()->create(['role' => 'petugas']);
    $barang = barangUji('PJ-004', 5);
    $fotoBesar = fotoBuktiPalsu('besar.png')->size(2049);

    $response = $this->actingAs($petugas)->post(route('petugas.peminjaman.store'), [
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Irfan',
        'jumlah' => 1,
        'gambar_bukti_pinjam' => $fotoBesar,
    ]);

    $response->assertSessionHasErrors('gambar_bukti_pinjam');
    $this->assertDatabaseCount('peminjaman', 0);
});

it('removes the uploaded loan proof when stock is insufficient', function () {
    Storage::fake('public');
    $petugas = User::factory()->create(['role' => 'petugas']);
    $barang = barangUji('PJ-005', 1);

    $response = $this->actingAs($petugas)->post(route('petugas.peminjaman.store'), [
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Joko',
        'jumlah' => 3,
        'gambar_bukti_pinjam' => fotoBuktiPalsu(),
    ]);

    $response->assertSessionHasErrors('jumlah');
    $this->assertDatabaseCount('peminjaman', 0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('stores a return proof photo when a loan is returned', function () {
    Storage::fake('public');
    $petugas = User::factory()->create(['role' => 'petugas']);
    $barang = barangUji('PJ-006', 0, 1);
    $peminjaman = Peminjaman::query()->create([
        'nomor_peminjaman' => 'PM-UJI-FOTO',
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Kirana',
        'jumlah' => 1,
        'tanggal_pinjam' => now()->subDay(),
        'kondisi_saat_dipinjam' => 'baik',
        'status' => 'dipinjam',
        'user_id' => $petugas->id,
    ]);

    $response = $this->actingAs($petugas)->patch(route('petugas.peminjaman.return', $peminjaman), [
        'kondisi_saat_dikembalikan' => 'rusak_ringan',
        'gambar_bukti_kembali' => fotoBuktiPalsu('kembali.png'),
    ]);

    $response->assertRedirect(route('petugas.peminjaman.index'));
    $peminjaman->refresh();
    expect($peminjaman->status)->toBe('dikembalikan')
        ->and($peminjaman->gambar_bukti_kembali)->not->toBeNull();
    Storage::disk('public')->assertExists($peminjaman->gambar_bukti_kembali);
    $this->assertDatabaseHas('barang', ['id' => $barang->id, 'stok_dipinjam' => 0, 'stok_rusak_ringan' => 1]);
});

it('removes the uploaded return proof when the loan was already returned', function () {
    Storage::fake('public');
    $petugas = User::factory()->create(['role' => 'petugas']);
    $barang = barangUji('PJ-007', 1);
    $peminjaman = Peminjaman::query()->create([
        'nomor_peminjaman' => 'PM-UJI-SUDAH',
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Lukas',
        'jumlah' => 1,
        'tanggal_pinjam' => now()->subDay(),
        'tanggal_kembali' => now(),
        'kondisi_saat_dipinjam' => 'baik',
        'kondisi_saat_dikembalikan' => 'baik',
        'status' => 'dikembalikan',
        'user_id' => $petugas->id,
    ]);

    $response = $this->actingAs($petugas)->patch(route('petugas.peminjaman.return', $peminjaman), [
        'kondisi_saat_dikembalikan' => 'baik',
        'gambar_bukti_kembali' => fotoBuktiPalsu('kembali.png'),
    ]);

    $response->assertSessionHasErrors('peminjaman');
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('renders the loan page with upload fields and a return button for active loans', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $barang = barangUji('PJ-008', 4, 1);
    Peminjaman::query()->create([
        'nomor_peminjaman' => 'PM-UJI-AKTIF',
        'barang_id' => $barang->id,
        'peminjam_nama' => 'Maya',
        'jumlah' => 1,
        'tanggal_pinjam' => now()->subDays(2),
        'kondisi_saat_dipinjam' => 'baik',
        'status' => 'dipinjam',
        'user_id' => $petugas->id,
    ]);

    $this->actingAs($petugas)
        ->get(route('petugas.peminjaman.index'))
        ->assertOk()
        ->assertSee('name="tanggal_pinjam"', false)
        ->assertSee('name="gambar_bukti_pinjam"', false)
        ->assertSee('name="gambar_bukti_kembali"', false)
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee('data-tx-return-btn', false)
        ->assertSeeText('PM-UJI-AKTIF')
        ->assertSeeText('Proses pengembalian');
});

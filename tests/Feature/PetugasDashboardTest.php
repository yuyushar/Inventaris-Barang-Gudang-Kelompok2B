<?php

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\RiwayatStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPetugasDashboardItem(string $code, string $name, array $attributes = []): Barang
{
    $kategori = Kategori::query()->firstOrCreate(['nama_kategori' => 'Kategori Petugas']);

    return Barang::query()->create(array_merge([
        'kode_barang' => $code,
        'nama_barang' => $name,
        'kategori_id' => $kategori->id,
        'satuan' => 'unit',
    ], $attributes));
}

function createPetugasDashboardLog(Barang $barang, User $user, string $jenis, int $jumlah, $tanggal): RiwayatStok
{
    return RiwayatStok::query()->create([
        'barang_id' => $barang->id,
        'user_id' => $user->id,
        'jenis_transaksi' => $jenis,
        'kondisi' => 'baik',
        'jumlah' => $jumlah,
        'tanggal' => $tanggal,
    ]);
}

function createPetugasDashboardLoan(Barang $barang, User $user, string $nomor, string $peminjam, string $status, $tanggal): Peminjaman
{
    return Peminjaman::query()->create([
        'nomor_peminjaman' => $nomor,
        'barang_id' => $barang->id,
        'peminjam_nama' => $peminjam,
        'jumlah' => 1,
        'tanggal_pinjam' => $tanggal,
        'kondisi_saat_dipinjam' => 'baik',
        'status' => $status,
        'user_id' => $user->id,
    ]);
}

it('redirects guests to the login page', function () {
    $this->get(route('petugas.dashboard'))->assertRedirect(route('login'));
});

it('forbids admin from opening the petugas dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('petugas.dashboard'))
        ->assertForbidden();
});

it('links to every transaction page as a quick shortcut', function () {
    $petugas = User::factory()->create(['role' => 'petugas', 'name' => 'Petugas Satu']);

    $this->actingAs($petugas)
        ->get(route('petugas.dashboard'))
        ->assertOk()
        ->assertSeeText('Dashboard Petugas')
        ->assertSeeText('Petugas Satu')
        ->assertSee(route('petugas.barang-masuk.index'))
        ->assertSee(route('petugas.barang-keluar.index'))
        ->assertSee(route('petugas.peminjaman.index'))
        ->assertSee(route('petugas.riwayat-stok.index'));
});

it('shows friendly empty states when there is nothing in the queue', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);

    $this->actingAs($petugas)
        ->get(route('petugas.dashboard'))
        ->assertOk()
        ->assertSeeText('Tidak ada peminjaman yang menunggu pengembalian.')
        ->assertSeeText('Semua stok masih aman, tidak ada yang perlu direstok.')
        ->assertSeeText('Kamu belum mencatat aktivitas apa pun.');
});

it('counts only the signed in petugas activity from today', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $rekan = User::factory()->create(['role' => 'petugas']);
    $barang = createPetugasDashboardItem('PTG-001', 'Kabel HDMI', ['stok_baik' => 50]);

    createPetugasDashboardLog($barang, $petugas, 'masuk', 3, now());
    createPetugasDashboardLog($barang, $petugas, 'masuk', 4, now());
    createPetugasDashboardLog($barang, $petugas, 'keluar', 2, now());
    createPetugasDashboardLog($barang, $rekan, 'masuk', 100, now());
    createPetugasDashboardLog($barang, $petugas, 'masuk', 50, now()->subDay());

    $this->actingAs($petugas)
        ->get(route('petugas.dashboard'))
        ->assertOk()
        ->assertViewHas('kartu', fn (array $kartu): bool => $kartu['masuk']['nilai'] === 2
            && $kartu['masuk']['keterangan'] === '7 unit'
            && $kartu['keluar']['nilai'] === 1
            && $kartu['keluar']['keterangan'] === '2 unit'
            && $kartu['peminjaman']['nilai'] === 0
            && $kartu['pengembalian']['nilai'] === 0);
});

it('queues unreturned loans with the oldest first and leaves out returned ones', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $barang = createPetugasDashboardItem('PTG-002', 'Proyektor', ['stok_baik' => 5, 'stok_dipinjam' => 2]);

    createPetugasDashboardLoan($barang, $petugas, 'PM-PTG-001', 'Budi Baru', 'dipinjam', now()->subDay());
    createPetugasDashboardLoan($barang, $petugas, 'PM-PTG-002', 'Sari Lama', 'dipinjam', now()->subDays(5));
    createPetugasDashboardLoan($barang, $petugas, 'PM-PTG-003', 'Dedi Kembali', 'dikembalikan', now()->subDays(9));

    $this->actingAs($petugas)
        ->get(route('petugas.dashboard'))
        ->assertOk()
        ->assertViewHas('jumlahPeminjamanAktif', 2)
        ->assertViewHas('peminjamanAktif', fn ($daftar): bool => $daftar->pluck('peminjam_nama')->all() === ['Sari Lama', 'Budi Baru'])
        ->assertSeeText('Sari Lama')
        ->assertSeeText('5 hari')
        ->assertDontSeeText('Dedi Kembali');
});

it('queues only items at or below their minimum stock for restocking', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    createPetugasDashboardItem('PTG-003', 'Kabel Menipis', ['stok_baik' => 1, 'stok_minimum' => 5]);
    createPetugasDashboardItem('PTG-004', 'Router Aman', ['stok_baik' => 20, 'stok_minimum' => 2]);

    $this->actingAs($petugas)
        ->get(route('petugas.dashboard'))
        ->assertOk()
        ->assertViewHas('jumlahMenipis', 1)
        ->assertSeeText('Kabel Menipis')
        ->assertDontSeeText('Router Aman');
});

it('lists the latest activity of the signed in petugas only', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);
    $rekan = User::factory()->create(['role' => 'petugas']);
    $barangSaya = createPetugasDashboardItem('PTG-005', 'Barang Saya', ['stok_baik' => 10, 'stok_minimum' => 1]);
    $barangRekan = createPetugasDashboardItem('PTG-006', 'Barang Rekan', ['stok_baik' => 10, 'stok_minimum' => 1]);

    createPetugasDashboardLog($barangSaya, $petugas, 'masuk', 1, now());
    createPetugasDashboardLog($barangRekan, $rekan, 'keluar', 1, now());

    $this->actingAs($petugas)
        ->get(route('petugas.dashboard'))
        ->assertOk()
        ->assertSeeText('Barang Saya')
        ->assertDontSeeText('Barang Rekan');
});

<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\RiwayatStok;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    private const JUMLAH_HARI_GRAFIK = 7;

    private const BATAS_DAFTAR = 5;

    public function __invoke(): View
    {
        $ringkasan = Barang::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total_jenis')
            ->selectRaw('COALESCE(SUM(stok_baik), 0) as total_baik')
            ->selectRaw('COALESCE(SUM(stok_rusak_ringan), 0) as total_rusak_ringan')
            ->selectRaw('COALESCE(SUM(stok_rusak), 0) as total_rusak')
            ->selectRaw('COALESCE(SUM(stok_dipinjam), 0) as total_dipinjam')
            ->selectRaw('COALESCE(SUM(CASE WHEN stok_baik <= stok_minimum THEN 1 ELSE 0 END), 0) as total_menipis')
            ->first();

        $totalBaik = (int) $ringkasan->total_baik;
        $totalRusakRingan = (int) $ringkasan->total_rusak_ringan;
        $totalRusak = (int) $ringkasan->total_rusak;
        $totalDipinjam = (int) $ringkasan->total_dipinjam;

        $jumlahKategori = Kategori::query()->count();
        $jumlahSupplier = Supplier::query()->count();
        $jumlahPeminjamanAktif = Peminjaman::query()->where('status', 'dipinjam')->count();

        $daftarHari = collect(range(self::JUMLAH_HARI_GRAFIK - 1, 0))
            ->map(fn (int $hariLalu): Carbon => today()->subDays($hariLalu));

        $jumlahMasuk = $this->jumlahPerHari(BarangMasuk::query(), $daftarHari);
        $jumlahKeluar = $this->jumlahPerHari(BarangKeluar::query(), $daftarHari);

        $stokPerKategori = Kategori::query()
            ->toBase()
            ->leftJoin('barang', 'barang.kategori_id', '=', 'kategori.id')
            ->selectRaw('kategori.nama_kategori')
            ->selectRaw('COALESCE(SUM(barang.stok_baik + barang.stok_rusak_ringan + barang.stok_rusak + barang.stok_dipinjam), 0) as total_stok')
            ->groupBy('kategori.id', 'kategori.nama_kategori')
            ->orderByDesc('total_stok')
            ->orderBy('kategori.nama_kategori')
            ->limit(6)
            ->get();

        $kartu = [
            'jenis_barang' => [
                'label' => 'Jenis barang',
                'nilai' => (int) $ringkasan->total_jenis,
                'keterangan' => $jumlahKategori.' kategori · '.$jumlahSupplier.' supplier',
                'warna' => 'blue',
            ],
            'stok_baik' => [
                'label' => 'Stok baik (tersedia)',
                'nilai' => $totalBaik,
                'keterangan' => 'Siap dipakai atau dipinjam',
                'warna' => 'green',
            ],
            'dipinjam' => [
                'label' => 'Sedang dipinjam',
                'nilai' => $totalDipinjam,
                'keterangan' => $jumlahPeminjamanAktif.' transaksi peminjaman aktif',
                'warna' => 'slate',
            ],
            'rusak' => [
                'label' => 'Barang rusak',
                'nilai' => $totalRusakRingan + $totalRusak,
                'keterangan' => $totalRusakRingan.' rusak ringan · '.$totalRusak.' rusak',
                'warna' => 'amber',
            ],
            'menipis' => [
                'label' => 'Stok menipis',
                'nilai' => (int) $ringkasan->total_menipis,
                'keterangan' => 'Stok baik ≤ stok minimum',
                'warna' => 'red',
            ],
            'masuk_7_hari' => [
                'label' => 'Unit masuk ('.self::JUMLAH_HARI_GRAFIK.' hari)',
                'nilai' => array_sum($jumlahMasuk),
                'keterangan' => 'Keluar: '.number_format(array_sum($jumlahKeluar), 0, ',', '.').' unit',
                'warna' => 'blue',
            ],
        ];

        $grafik = [
            'transaksi' => [
                'labels' => $daftarHari->map(fn (Carbon $hari): string => $hari->format('d/m'))->all(),
                'masuk' => $jumlahMasuk,
                'keluar' => $jumlahKeluar,
            ],
            'kondisi' => [
                ['label' => 'Baik', 'nilai' => $totalBaik, 'warna' => '#16a34a'],
                ['label' => 'Rusak ringan', 'nilai' => $totalRusakRingan, 'warna' => '#d97706'],
                ['label' => 'Rusak', 'nilai' => $totalRusak, 'warna' => '#dc2626'],
                ['label' => 'Dipinjam', 'nilai' => $totalDipinjam, 'warna' => '#2563eb'],
            ],
            'kategori' => [
                'labels' => $stokPerKategori->pluck('nama_kategori')->all(),
                'data' => $stokPerKategori->map(fn (object $baris): int => (int) $baris->total_stok)->all(),
            ],
        ];

        $barangMenipis = Barang::query()
            ->whereColumn('stok_baik', '<=', 'stok_minimum')
            ->orderBy('stok_baik')
            ->orderBy('nama_barang')
            ->limit(self::BATAS_DAFTAR)
            ->get(['id', 'kode_barang', 'nama_barang', 'satuan', 'stok_baik', 'stok_minimum']);

        $peminjamanAktif = Peminjaman::query()
            ->with('barang:id,kode_barang,nama_barang,satuan')
            ->where('status', 'dipinjam')
            ->orderByDesc('tanggal_pinjam')
            ->orderByDesc('id')
            ->limit(self::BATAS_DAFTAR)
            ->get();

        $aktivitasTerbaru = RiwayatStok::query()
            ->with([
                'barang:id,kode_barang,nama_barang,satuan',
                'user:id,name',
            ])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(self::BATAS_DAFTAR + 1)
            ->get();

        return view('admin.dashboard', [
            'kartu' => $kartu,
            'grafik' => $grafik,
            'barangMenipis' => $barangMenipis,
            'peminjamanAktif' => $peminjamanAktif,
            'aktivitasTerbaru' => $aktivitasTerbaru,
        ]);
    }

    /**
     * Total jumlah per hari untuk setiap hari pada daftar, hari tanpa transaksi bernilai 0.
     *
     * @param  Builder<BarangMasuk|BarangKeluar>  $query
     * @param  Collection<int, Carbon>  $daftarHari
     * @return list<int>
     */
    private function jumlahPerHari(Builder $query, Collection $daftarHari): array
    {
        $totalPerHari = $query
            ->whereBetween('tanggal', [
                $daftarHari->first()->copy()->startOfDay(),
                $daftarHari->last()->copy()->endOfDay(),
            ])
            ->toBase()
            ->selectRaw('DATE(tanggal) as hari')
            ->selectRaw('SUM(jumlah) as total')
            ->groupBy('hari')
            ->pluck('total', 'hari');

        return $daftarHari
            ->map(fn (Carbon $hari): int => (int) ($totalPerHari[$hari->toDateString()] ?? 0))
            ->values()
            ->all();
    }
}

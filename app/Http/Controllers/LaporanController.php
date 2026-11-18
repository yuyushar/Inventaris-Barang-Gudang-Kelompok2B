<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    private const BATAS_PRATINJAU = 200;

    private const JENIS_TRANSAKSI = [
        'masuk' => 'Barang masuk',
        'keluar' => 'Barang keluar',
        'penyesuaian' => 'Penyesuaian stok',
        'peminjaman' => 'Peminjaman',
        'pengembalian' => 'Pengembalian',
    ];

    private const NAMA_KONDISI = [
        'baik' => 'Baik',
        'rusak_ringan' => 'Rusak ringan',
        'rusak' => 'Rusak',
    ];

    public function index(Request $request): View
    {
        $filters = $this->validasiFilter($request);

        return view('admin.laporan', [
            'filters' => $filters,
            'laporan' => $this->susunLaporan($filters, self::BATAS_PRATINJAU),
            'kategoris' => Kategori::query()->orderBy('nama_kategori')->get(['id', 'nama_kategori']),
            'jenisTransaksi' => self::JENIS_TRANSAKSI,
            'parameterUnduh' => $this->parameterUnduh($filters),
        ]);
    }

    public function cetak(Request $request): View
    {
        $filters = $this->validasiFilter($request);

        return view('admin.laporan_cetak', [
            'laporan' => $this->susunLaporan($filters),
            'pencetak' => $request->user()->name,
            'waktuCetak' => now(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validasiFilter($request);
        $laporan = $this->susunLaporan($filters);
        $namaFile = 'laporan-'.$filters['jenis_laporan'].'-'.today()->toDateString().'.csv';

        return response()->streamDownload(function () use ($laporan): void {
            $keluaran = fopen('php://output', 'w');

            fwrite($keluaran, "\xEF\xBB\xBF");
            fwrite($keluaran, "sep=;\r\n");
            $this->tulisBarisCsv($keluaran, [$laporan['judul']]);
            $this->tulisBarisCsv($keluaran, [$laporan['periode']]);

            foreach ($laporan['filter'] as $keteranganFilter) {
                $this->tulisBarisCsv($keluaran, [$keteranganFilter]);
            }

            $this->tulisBarisCsv($keluaran, []);
            $this->tulisBarisCsv($keluaran, $laporan['kolom']);

            foreach ($laporan['baris'] as $baris) {
                $this->tulisBarisCsv($keluaran, array_map($this->amankanSel(...), $baris));
            }

            fclose($keluaran);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{jenis_laporan: string, kategori_id?: int|string|null, jenis_transaksi?: string|null, tanggal_dari?: string|null, tanggal_sampai?: string|null}
     */
    private function validasiFilter(Request $request): array
    {
        $filters = $request->validate([
            'jenis_laporan' => ['nullable', 'in:stok,mutasi'],
            'kategori_id' => ['nullable', 'integer', 'exists:kategori,id'],
            'jenis_transaksi' => ['nullable', 'in:masuk,keluar,penyesuaian,peminjaman,pengembalian'],
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date', 'after_or_equal:tanggal_dari'],
        ]);

        $filters['jenis_laporan'] ??= 'stok';

        return $filters;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function parameterUnduh(array $filters): array
    {
        return array_filter($filters, fn (mixed $nilai): bool => $nilai !== null && $nilai !== '');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     jenis: string,
     *     judul: string,
     *     periode: string,
     *     filter: list<string>,
     *     kolom: list<string>,
     *     baris: list<list<int|string>>,
     *     ringkasan: list<array{label: string, nilai: int, warna: string}>,
     *     total: int
     * }
     */
    private function susunLaporan(array $filters, ?int $batas = null): array
    {
        $keteranganFilter = [];

        if (! empty($filters['kategori_id'])) {
            $keteranganFilter[] = 'Kategori: '.Kategori::query()->whereKey($filters['kategori_id'])->value('nama_kategori');
        }

        if ($filters['jenis_laporan'] === 'mutasi') {
            return $this->susunLaporanMutasi($filters, $keteranganFilter, $batas);
        }

        return $this->susunLaporanStok($filters, $keteranganFilter, $batas);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $keteranganFilter
     * @return array<string, mixed>
     */
    private function susunLaporanStok(array $filters, array $keteranganFilter, ?int $batas): array
    {
        $daftarBarang = Barang::query()
            ->with('kategori:id,nama_kategori')
            ->when(
                $filters['kategori_id'] ?? null,
                fn ($query, $kategoriId) => $query->where('kategori_id', $kategoriId),
            )
            ->orderBy('nama_barang')
            ->get();

        $baris = [];

        foreach ($daftarBarang->take($batas ?? PHP_INT_MAX)->values() as $urutan => $barang) {
            $total = $barang->stok_baik + $barang->stok_rusak_ringan + $barang->stok_rusak + $barang->stok_dipinjam;

            $baris[] = [
                $urutan + 1,
                $barang->kode_barang,
                $barang->nama_barang,
                $barang->kategori?->nama_kategori ?? '-',
                $barang->satuan,
                $barang->stok_baik,
                $barang->stok_rusak_ringan,
                $barang->stok_rusak,
                $barang->stok_dipinjam,
                $total,
                $barang->stok_minimum,
                $barang->stok_baik <= $barang->stok_minimum ? 'Perlu perhatian' : 'Aman',
            ];
        }

        return [
            'jenis' => 'stok',
            'judul' => 'Laporan Stok Barang',
            'periode' => 'Posisi stok per '.now()->format('d/m/Y H:i'),
            'filter' => $keteranganFilter,
            'kolom' => ['No', 'Kode barang', 'Nama barang', 'Kategori', 'Satuan', 'Stok baik', 'Rusak ringan', 'Rusak', 'Dipinjam', 'Total', 'Stok minimum', 'Status'],
            'baris' => $baris,
            'ringkasan' => [
                ['label' => 'Jenis barang', 'nilai' => $daftarBarang->count(), 'warna' => 'blue'],
                ['label' => 'Stok baik', 'nilai' => (int) $daftarBarang->sum('stok_baik'), 'warna' => 'green'],
                ['label' => 'Rusak', 'nilai' => (int) ($daftarBarang->sum('stok_rusak_ringan') + $daftarBarang->sum('stok_rusak')), 'warna' => 'amber'],
                ['label' => 'Sedang dipinjam', 'nilai' => (int) $daftarBarang->sum('stok_dipinjam'), 'warna' => 'slate'],
                ['label' => 'Stok menipis', 'nilai' => $daftarBarang->filter(fn (Barang $barang): bool => $barang->stok_baik <= $barang->stok_minimum)->count(), 'warna' => 'red'],
            ],
            'total' => $daftarBarang->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $keteranganFilter
     * @return array<string, mixed>
     */
    private function susunLaporanMutasi(array $filters, array $keteranganFilter, ?int $batas): array
    {
        $query = RiwayatStok::query()
            ->with(['barang:id,kode_barang,nama_barang,satuan', 'user:id,name'])
            ->when(
                $filters['jenis_transaksi'] ?? null,
                fn ($query, $jenis) => $query->where('jenis_transaksi', $jenis),
            )
            ->when(
                $filters['kategori_id'] ?? null,
                fn ($query, $kategoriId) => $query->whereHas('barang', fn ($barang) => $barang->where('kategori_id', $kategoriId)),
            )
            ->when(
                $filters['tanggal_dari'] ?? null,
                fn ($query, $tanggal) => $query->whereDate('tanggal', '>=', $tanggal),
            )
            ->when(
                $filters['tanggal_sampai'] ?? null,
                fn ($query, $tanggal) => $query->whereDate('tanggal', '<=', $tanggal),
            );

        if (! empty($filters['jenis_transaksi'])) {
            $keteranganFilter[] = 'Aktivitas: '.self::JENIS_TRANSAKSI[$filters['jenis_transaksi']];
        }

        $agregat = (clone $query)
            ->toBase()
            ->reorder()
            ->selectRaw('jenis_transaksi')
            ->selectRaw('COUNT(*) as total_transaksi')
            ->selectRaw('COALESCE(SUM(jumlah), 0) as total_unit')
            ->groupBy('jenis_transaksi')
            ->get()
            ->keyBy('jenis_transaksi');

        $totalCatatan = (int) $agregat->sum('total_transaksi');
        $unit = fn (string $jenis): int => (int) ($agregat->get($jenis)->total_unit ?? 0);

        $catatan = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->when($batas, fn ($query, int $batas) => $query->limit($batas))
            ->get();

        $baris = [];

        foreach ($catatan->values() as $urutan => $log) {
            $baris[] = [
                $urutan + 1,
                $log->tanggal?->format('d/m/Y H:i') ?? '-',
                self::JENIS_TRANSAKSI[$log->jenis_transaksi] ?? $log->jenis_transaksi,
                $log->barang?->kode_barang ?? '-',
                $log->barang?->nama_barang ?? '-',
                self::NAMA_KONDISI[$log->kondisi] ?? $log->kondisi,
                $log->jumlah,
                $log->barang?->satuan ?? '-',
                $log->user?->name ?? 'Sistem',
                $log->keterangan ?? '-',
            ];
        }

        $dari = ! empty($filters['tanggal_dari']) ? date('d/m/Y', strtotime($filters['tanggal_dari'])) : null;
        $sampai = ! empty($filters['tanggal_sampai']) ? date('d/m/Y', strtotime($filters['tanggal_sampai'])) : null;

        return [
            'jenis' => 'mutasi',
            'judul' => 'Laporan Mutasi Stok',
            'periode' => match (true) {
                $dari !== null && $sampai !== null => 'Periode: '.$dari.' s.d. '.$sampai,
                $dari !== null => 'Periode: sejak '.$dari,
                $sampai !== null => 'Periode: sampai '.$sampai,
                default => 'Periode: semua tanggal',
            },
            'filter' => $keteranganFilter,
            'kolom' => ['No', 'Waktu', 'Aktivitas', 'Kode barang', 'Nama barang', 'Kondisi', 'Jumlah', 'Satuan', 'Petugas', 'Keterangan'],
            'baris' => $baris,
            'ringkasan' => [
                ['label' => 'Total catatan', 'nilai' => $totalCatatan, 'warna' => 'blue'],
                ['label' => 'Unit masuk', 'nilai' => $unit('masuk'), 'warna' => 'green'],
                ['label' => 'Unit keluar', 'nilai' => $unit('keluar'), 'warna' => 'amber'],
                ['label' => 'Unit dipinjamkan', 'nilai' => $unit('peminjaman'), 'warna' => 'slate'],
                ['label' => 'Unit dikembalikan', 'nilai' => $unit('pengembalian'), 'warna' => 'blue'],
            ],
            'total' => $totalCatatan,
        ];
    }

    /**
     * Awalan apostrof mencegah Excel menjalankan isi sel sebagai rumus (CSV injection).
     */
    private function amankanSel(int|string $nilai): int|string
    {
        if (is_int($nilai)) {
            return $nilai;
        }

        return preg_match('/^[=+\-@\t\r]/', $nilai) === 1 ? "'".$nilai : $nilai;
    }

    /**
     * @param  resource  $keluaran
     * @param  list<int|string>  $baris
     */
    private function tulisBarisCsv($keluaran, array $baris): void
    {
        fputcsv($keluaran, $baris, ';', '"', '\\', "\r\n");
    }
}

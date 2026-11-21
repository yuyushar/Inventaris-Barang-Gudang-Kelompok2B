<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Peminjaman;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetugasDashboardController extends Controller
{
    private const BATAS_DAFTAR = 5;

    public function __invoke(Request $request): View
    {
        $petugasId = $request->user()->id;

        $aktivitasHariIni = RiwayatStok::query()
            ->where('user_id', $petugasId)
            ->whereBetween('tanggal', [today(), today()->endOfDay()])
            ->toBase()
            ->selectRaw('jenis_transaksi')
            ->selectRaw('COUNT(*) as total_transaksi')
            ->selectRaw('SUM(jumlah) as total_unit')
            ->groupBy('jenis_transaksi')
            ->get()
            ->keyBy('jenis_transaksi');

        $jenisKartu = [
            'masuk' => ['Masuk hari ini', 'green'],
            'keluar' => ['Keluar hari ini', 'amber'],
            'peminjaman' => ['Dipinjamkan hari ini', 'blue'],
            'pengembalian' => ['Dikembalikan hari ini', 'slate'],
        ];

        $kartu = [];

        foreach ($jenisKartu as $jenis => [$label, $warna]) {
            $baris = $aktivitasHariIni->get($jenis);

            $kartu[$jenis] = [
                'label' => $label,
                'nilai' => (int) ($baris->total_transaksi ?? 0),
                'keterangan' => number_format((int) ($baris->total_unit ?? 0), 0, ',', '.').' unit',
                'warna' => $warna,
            ];
        }

        $peminjamanAktif = Peminjaman::query()
            ->with('barang:id,kode_barang,nama_barang,satuan')
            ->where('status', 'dipinjam')
            ->orderBy('tanggal_pinjam')
            ->orderBy('id')
            ->limit(self::BATAS_DAFTAR)
            ->get();

        $barangMenipis = Barang::query()
            ->whereColumn('stok_baik', '<=', 'stok_minimum')
            ->orderBy('stok_baik')
            ->orderBy('nama_barang')
            ->limit(self::BATAS_DAFTAR)
            ->get(['id', 'kode_barang', 'nama_barang', 'satuan', 'stok_baik', 'stok_minimum']);

        $aktivitasSaya = RiwayatStok::query()
            ->with('barang:id,kode_barang,nama_barang,satuan')
            ->where('user_id', $petugasId)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(self::BATAS_DAFTAR)
            ->get();

        return view('petugas.dashboard', [
            'kartu' => $kartu,
            'jumlahPeminjamanAktif' => Peminjaman::query()->where('status', 'dipinjam')->count(),
            'jumlahMenipis' => Barang::query()->whereColumn('stok_baik', '<=', 'stok_minimum')->count(),
            'peminjamanAktif' => $peminjamanAktif,
            'barangMenipis' => $barangMenipis,
            'aktivitasSaya' => $aktivitasSaya,
        ]);
    }
}

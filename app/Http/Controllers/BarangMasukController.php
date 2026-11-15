<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\RiwayatStok;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BarangMasukController extends Controller
{
    public function index(): View
    {
        return view('petugas.barang_masuk', [
            'barangs' => Barang::query()
                ->orderBy('nama_barang')
                ->get(['id', 'kode_barang', 'nama_barang', 'satuan', 'stok_baik']),
            'suppliers' => Supplier::query()
                ->orderBy('nama_supplier')
                ->get(['id', 'nama_supplier']),
            'barangMasuks' => BarangMasuk::query()
                ->with(['barang', 'supplier', 'user'])
                ->latest('tanggal')
                ->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:supplier,id'],
            'barang_id' => ['required', 'exists:barang,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
        ], [
            'jumlah.min' => 'Jumlah barang masuk minimal 1.',
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $barang = Barang::query()
                ->lockForUpdate()
                ->findOrFail($validated['barang_id']);
            $nomorTransaksi = 'BM-'.Str::ulid();

            BarangMasuk::query()->create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal' => $validated['tanggal'],
                'supplier_id' => $validated['supplier_id'],
                'barang_id' => $barang->id,
                'jumlah' => $validated['jumlah'],
                'user_id' => $request->user()->id,
            ]);

            $barang->increment('stok_baik', $validated['jumlah']);

            RiwayatStok::query()->create([
                'barang_id' => $barang->id,
                'user_id' => $request->user()->id,
                'jenis_transaksi' => 'masuk',
                'kondisi' => 'baik',
                'jumlah' => $validated['jumlah'],
                'tanggal' => $validated['tanggal'],
                'keterangan' => 'Nomor transaksi: '.$nomorTransaksi,
            ]);
        });

        return redirect()
            ->route('petugas.barang-masuk.index')
            ->with('success', 'Transaksi barang masuk berhasil disimpan.');
    }
}

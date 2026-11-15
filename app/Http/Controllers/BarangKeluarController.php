<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BarangKeluarController extends Controller
{
    public function index(): View
    {
        return view('petugas.barang_keluar', [
            'barangs' => Barang::query()
                ->where('stok_baik', '>', 0)
                ->orderBy('nama_barang')
                ->get(['id', 'kode_barang', 'nama_barang', 'satuan', 'stok_baik']),
            'barangKeluars' => BarangKeluar::query()
                ->with(['barang', 'user'])
                ->latest('tanggal')
                ->paginate(10),
        ]);
    }

    public function store(Request $request, StockMovementService $stockMovement): RedirectResponse
    {
        $validated = $request->validate([
            'barang_id' => ['required', 'exists:barang,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tujuan' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
        ], [
            'jumlah.min' => 'Jumlah barang keluar minimal 1.',
        ]);

        DB::transaction(function () use ($validated, $request, $stockMovement): void {
            $nomorTransaksi = 'BK-'.Str::ulid();

            BarangKeluar::query()->create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal' => $validated['tanggal'],
                'barang_id' => $validated['barang_id'],
                'jumlah' => $validated['jumlah'],
                'tujuan' => $validated['tujuan'],
                'user_id' => $request->user()->id,
            ]);

            $stockMovement->record(
                $validated['barang_id'],
                'keluar',
                'baik',
                $validated['jumlah'],
                $request->user(),
                $validated['tanggal'],
                'Nomor transaksi: '.$nomorTransaksi.'; Tujuan: '.$validated['tujuan'],
            );
        });

        return redirect()
            ->route('petugas.barang-keluar.index')
            ->with('success', 'Transaksi barang keluar berhasil disimpan.');
    }
}

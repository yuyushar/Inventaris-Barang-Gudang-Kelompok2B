<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Supplier;
use App\Services\StockMovementService;
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

    public function store(Request $request, StockMovementService $stockMovement): RedirectResponse
    {
        if ($request->has('items')) {
            return $this->storeMultiItem($request, $stockMovement);
        }

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:supplier,id'],
            'barang_id' => ['required', 'exists:barang,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
        ], [
            'jumlah.min' => 'Jumlah barang masuk minimal 1.',
        ]);

        DB::transaction(function () use ($validated, $request, $stockMovement): void {
            $nomorTransaksi = 'BM-'.Str::ulid();

            BarangMasuk::query()->create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal' => $validated['tanggal'],
                'supplier_id' => $validated['supplier_id'],
                'barang_id' => $validated['barang_id'],
                'jumlah' => $validated['jumlah'],
                'user_id' => $request->user()->id,
            ]);

            $stockMovement->record(
                $validated['barang_id'],
                'masuk',
                'baik',
                $validated['jumlah'],
                $request->user(),
                $validated['tanggal'],
                'Nomor transaksi: '.$nomorTransaksi,
            );
        });

        return redirect()
            ->route('petugas.barang-masuk.index')
            ->with('success', 'Transaksi barang masuk berhasil disimpan.');
    }

    /**
     * FE 2 (Estu): simpan beberapa barang sekaligus dari form multi-item.
     * Satu supplier dan satu tanggal untuk semua baris; tiap baris tetap
     * mendapat nomor transaksi BM- sendiri. Semua baris disimpan dalam satu
     * transaksi database, jadi kalau satu gagal, semuanya dibatalkan.
     */
    private function storeMultiItem(Request $request, StockMovementService $stockMovement): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:supplier,id'],
            'tanggal' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.barang_id' => ['required', 'distinct', 'exists:barang,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ], [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak ditemukan.',
            'tanggal.required' => 'Tanggal diterima wajib diisi.',
            'tanggal.date' => 'Format tanggal diterima tidak valid.',
            'items.required' => 'Tambahkan minimal satu barang.',
            'items.array' => 'Daftar barang tidak valid.',
            'items.min' => 'Tambahkan minimal satu barang.',
            'items.max' => 'Maksimal 20 barang dalam satu transaksi.',
            'items.*.barang_id.required' => 'Pilih barang pada setiap baris.',
            'items.*.barang_id.distinct' => 'Barang yang sama tidak boleh dipilih lebih dari sekali.',
            'items.*.barang_id.exists' => 'Barang yang dipilih tidak ditemukan.',
            'items.*.jumlah.required' => 'Isi jumlah pada setiap baris.',
            'items.*.jumlah.integer' => 'Jumlah harus berupa bilangan bulat.',
            'items.*.jumlah.min' => 'Jumlah barang masuk minimal 1.',
        ]);

        DB::transaction(function () use ($validated, $request, $stockMovement): void {
            foreach ($validated['items'] as $item) {
                $nomorTransaksi = 'BM-'.Str::ulid();

                BarangMasuk::query()->create([
                    'nomor_transaksi' => $nomorTransaksi,
                    'tanggal' => $validated['tanggal'],
                    'supplier_id' => $validated['supplier_id'],
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'user_id' => $request->user()->id,
                ]);

                $stockMovement->record(
                    (int) $item['barang_id'],
                    'masuk',
                    'baik',
                    (int) $item['jumlah'],
                    $request->user(),
                    $validated['tanggal'],
                    'Nomor transaksi: '.$nomorTransaksi,
                );
            }
        });

        $jumlahBaris = count($validated['items']);

        return redirect()
            ->route('petugas.barang-masuk.index')
            ->with('success', $jumlahBaris.' jenis barang masuk berhasil disimpan.');
    }
}

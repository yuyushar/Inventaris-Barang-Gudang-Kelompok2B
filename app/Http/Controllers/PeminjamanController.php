<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Peminjaman;
use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    public function index(): View
    {
        return view('petugas.peminjaman', [
            'barangs' => Barang::query()
                ->where('stok_baik', '>', 0)
                ->orderBy('nama_barang')
                ->get(['id', 'kode_barang', 'nama_barang', 'satuan', 'stok_baik']),
            'peminjamans' => Peminjaman::query()
                ->with(['barang', 'user', 'pengembali'])
                ->latest('tanggal_pinjam')
                ->paginate(10),
        ]);
    }

    public function store(Request $request, StockMovementService $stockMovement): RedirectResponse
    {
        $validated = $request->validate([
            'barang_id' => ['required', 'exists:barang,id'],
            'peminjam_nama' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal_pinjam' => ['nullable', 'date', 'before_or_equal:now'],
            'gambar_bukti_pinjam' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'jumlah.min' => 'Jumlah peminjaman minimal 1.',
            'tanggal_pinjam.date' => 'Format tanggal pinjam tidak valid.',
            'tanggal_pinjam.before_or_equal' => 'Tanggal pinjam tidak boleh melebihi waktu sekarang.',
            'gambar_bukti_pinjam.image' => 'Foto bukti pinjam harus berupa gambar.',
            'gambar_bukti_pinjam.mimes' => 'Foto bukti pinjam harus berformat JPG, PNG, atau WebP.',
            'gambar_bukti_pinjam.max' => 'Ukuran foto bukti pinjam maksimal 2 MB.',
        ]);

        $tanggalPinjam = filled($validated['tanggal_pinjam'] ?? null)
            ? Carbon::parse($validated['tanggal_pinjam'])
            : now();

        DB::transaction(function () use ($validated, $request, $stockMovement, $tanggalPinjam): void {
            $nomorPeminjaman = 'PM-'.Str::ulid();

            $peminjaman = Peminjaman::query()->create([
                'nomor_peminjaman' => $nomorPeminjaman,
                'barang_id' => $validated['barang_id'],
                'peminjam_nama' => $validated['peminjam_nama'],
                'jumlah' => $validated['jumlah'],
                'tanggal_pinjam' => $tanggalPinjam,
                'kondisi_saat_dipinjam' => 'baik',
                'status' => 'dipinjam',
                'user_id' => $request->user()->id,
            ]);

            $stockMovement->record(
                $validated['barang_id'],
                'peminjaman',
                'baik',
                $validated['jumlah'],
                $request->user(),
                $tanggalPinjam,
                'Nomor peminjaman: '.$nomorPeminjaman.'; Peminjam: '.$validated['peminjam_nama'],
            );

            // FE 2 (Estu): foto disimpan setelah stok dipastikan cukup, jadi tidak ada file yatim.
            if ($request->hasFile('gambar_bukti_pinjam')) {
                $peminjaman->update([
                    'gambar_bukti_pinjam' => $request->file('gambar_bukti_pinjam')->store('bukti-peminjaman', 'public'),
                ]);
            }
        });

        return redirect()
            ->route('petugas.peminjaman.index')
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function return(
        Request $request,
        Peminjaman $peminjaman,
        StockMovementService $stockMovement,
    ): RedirectResponse {
        $validated = $request->validate([
            'kondisi_saat_dikembalikan' => ['required', 'in:baik,rusak_ringan,rusak'],
            'gambar_bukti_kembali' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'gambar_bukti_kembali.image' => 'Foto bukti kembali harus berupa gambar.',
            'gambar_bukti_kembali.mimes' => 'Foto bukti kembali harus berformat JPG, PNG, atau WebP.',
            'gambar_bukti_kembali.max' => 'Ukuran foto bukti kembali maksimal 2 MB.',
        ]);

        DB::transaction(function () use ($validated, $peminjaman, $request, $stockMovement): void {
            $peminjaman = Peminjaman::query()
                ->lockForUpdate()
                ->findOrFail($peminjaman->id);

            if ($peminjaman->status !== 'dipinjam') {
                throw ValidationException::withMessages([
                    'peminjaman' => 'Peminjaman ini sudah dikembalikan.',
                ]);
            }

            $kondisi = $validated['kondisi_saat_dikembalikan'];
            $tanggalKembali = now();

            $peminjaman->update([
                'tanggal_kembali' => $tanggalKembali,
                'kondisi_saat_dikembalikan' => $kondisi,
                'user_pengembali_id' => $request->user()->id,
                'status' => 'dikembalikan',
            ]);

            $stockMovement->record(
                $peminjaman->barang_id,
                'pengembalian',
                $kondisi,
                $peminjaman->jumlah,
                $request->user(),
                $tanggalKembali,
                'Nomor peminjaman: '.$peminjaman->nomor_peminjaman.'; Peminjam: '.$peminjaman->peminjam_nama,
            );

            if ($request->hasFile('gambar_bukti_kembali')) {
                $peminjaman->update([
                    'gambar_bukti_kembali' => $request->file('gambar_bukti_kembali')->store('bukti-peminjaman', 'public'),
                ]);
            }
        });

        return redirect()
            ->route('petugas.peminjaman.index')
            ->with('success', 'Pengembalian berhasil dicatat.');
    }
}

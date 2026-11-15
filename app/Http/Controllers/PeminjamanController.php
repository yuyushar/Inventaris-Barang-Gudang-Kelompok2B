<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Peminjaman;
use App\Models\RiwayatStok;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'barang_id' => ['required', 'exists:barang,id'],
            'peminjam_nama' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1'],
        ], [
            'jumlah.min' => 'Jumlah peminjaman minimal 1.',
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $barang = Barang::query()
                ->lockForUpdate()
                ->findOrFail($validated['barang_id']);

            if ($barang->stok_baik < $validated['jumlah']) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Stok barang baik tidak mencukupi. Stok tersedia: '.$barang->stok_baik.'.',
                ]);
            }

            $nomorPeminjaman = 'PM-'.Str::ulid();

            Peminjaman::query()->create([
                'nomor_peminjaman' => $nomorPeminjaman,
                'barang_id' => $barang->id,
                'peminjam_nama' => $validated['peminjam_nama'],
                'jumlah' => $validated['jumlah'],
                'tanggal_pinjam' => now(),
                'kondisi_saat_dipinjam' => 'baik',
                'status' => 'dipinjam',
                'user_id' => $request->user()->id,
            ]);

            $barang->decrement('stok_baik', $validated['jumlah']);
            $barang->increment('stok_dipinjam', $validated['jumlah']);

            RiwayatStok::query()->create([
                'barang_id' => $barang->id,
                'user_id' => $request->user()->id,
                'jenis_transaksi' => 'peminjaman',
                'kondisi' => 'baik',
                'jumlah' => $validated['jumlah'],
                'tanggal' => now(),
                'keterangan' => 'Nomor peminjaman: '.$nomorPeminjaman.'; Peminjam: '.$validated['peminjam_nama'],
            ]);
        });

        return redirect()
            ->route('petugas.peminjaman.index')
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function return(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $validated = $request->validate([
            'kondisi_saat_dikembalikan' => ['required', 'in:baik,rusak_ringan,rusak'],
        ]);

        DB::transaction(function () use ($validated, $peminjaman, $request): void {
            $peminjaman = Peminjaman::query()
                ->lockForUpdate()
                ->findOrFail($peminjaman->id);

            if ($peminjaman->status !== 'dipinjam') {
                throw ValidationException::withMessages([
                    'peminjaman' => 'Peminjaman ini sudah dikembalikan.',
                ]);
            }

            $barang = Barang::query()
                ->lockForUpdate()
                ->findOrFail($peminjaman->barang_id);

            if ($barang->stok_dipinjam < $peminjaman->jumlah) {
                throw ValidationException::withMessages([
                    'peminjaman' => 'Jumlah stok dipinjam tidak sesuai dengan data peminjaman. Hubungi administrator.',
                ]);
            }

            $kondisi = $validated['kondisi_saat_dikembalikan'];
            $kolomStok = match ($kondisi) {
                'baik' => 'stok_baik',
                'rusak_ringan' => 'stok_rusak_ringan',
                'rusak' => 'stok_rusak',
            };
            $tanggalKembali = now();

            $peminjaman->update([
                'tanggal_kembali' => $tanggalKembali,
                'kondisi_saat_dikembalikan' => $kondisi,
                'user_pengembali_id' => $request->user()->id,
                'status' => 'dikembalikan',
            ]);

            $barang->decrement('stok_dipinjam', $peminjaman->jumlah);
            $barang->increment($kolomStok, $peminjaman->jumlah);

            RiwayatStok::query()->create([
                'barang_id' => $barang->id,
                'user_id' => $request->user()->id,
                'jenis_transaksi' => 'pengembalian',
                'kondisi' => $kondisi,
                'jumlah' => $peminjaman->jumlah,
                'tanggal' => $tanggalKembali,
                'keterangan' => 'Nomor peminjaman: '.$peminjaman->nomor_peminjaman.'; Peminjam: '.$peminjaman->peminjam_nama,
            ]);
        });

        return redirect()
            ->route('petugas.peminjaman.index')
            ->with('success', 'Pengembalian berhasil dicatat.');
    }
}

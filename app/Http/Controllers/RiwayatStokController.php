<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\RiwayatStok;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiwayatStokController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'jenis_transaksi' => ['nullable', 'in:masuk,keluar,penyesuaian,peminjaman,pengembalian'],
            'barang_id' => ['nullable', 'integer', 'exists:barang,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date', 'after_or_equal:tanggal_dari'],
            'cari' => ['nullable', 'string', 'max:100'],
        ]);

        $riwayat = RiwayatStok::query()
            ->with([
                'barang:id,kode_barang,nama_barang,satuan',
                'user:id,name',
            ])
            ->when(
                $filters['jenis_transaksi'] ?? null,
                fn (Builder $query, string $jenisTransaksi) => $query->where('jenis_transaksi', $jenisTransaksi),
            )
            ->when(
                $filters['barang_id'] ?? null,
                fn (Builder $query, string $barangId) => $query->where('barang_id', $barangId),
            )
            ->when(
                $filters['user_id'] ?? null,
                fn (Builder $query, string $userId) => $query->where('user_id', $userId),
            )
            ->when(
                $filters['tanggal_dari'] ?? null,
                fn (Builder $query, string $tanggalDari) => $query->whereDate('tanggal', '>=', $tanggalDari),
            )
            ->when(
                $filters['tanggal_sampai'] ?? null,
                fn (Builder $query, string $tanggalSampai) => $query->whereDate('tanggal', '<=', $tanggalSampai),
            )
            ->when($filters['cari'] ?? null, function (Builder $query, string $cari): void {
                $term = '%'.$cari.'%';

                $query->where(function (Builder $searchQuery) use ($term): void {
                    $searchQuery
                        ->where('keterangan', 'like', $term)
                        ->orWhereHas('barang', function (Builder $barangQuery) use ($term): void {
                            $barangQuery
                                ->where('kode_barang', 'like', $term)
                                ->orWhere('nama_barang', 'like', $term);
                        })
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', $term));
                });
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('petugas.riwayat_stok', [
            'riwayat' => $riwayat,
            'barangs' => Barang::query()
                ->orderBy('nama_barang')
                ->get(['id', 'kode_barang', 'nama_barang']),
            'users' => User::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'filters' => $filters,
            'jenisTransaksi' => [
                'masuk' => 'Barang masuk',
                'keluar' => 'Barang keluar',
                'penyesuaian' => 'Penyesuaian stok',
                'peminjaman' => 'Peminjaman',
                'pengembalian' => 'Pengembalian',
            ],
        ]);
    }
}

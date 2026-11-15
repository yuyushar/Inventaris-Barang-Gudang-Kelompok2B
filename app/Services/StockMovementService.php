<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\RiwayatStok;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class StockMovementService
{
    public function record(
        int $barangId,
        string $jenisTransaksi,
        string $kondisi,
        int $jumlah,
        User $user,
        DateTimeInterface|string $tanggal,
        ?string $keterangan = null,
    ): RiwayatStok {
        if ($jumlah < 1) {
            throw new InvalidArgumentException('Jumlah perubahan stok harus lebih dari nol.');
        }

        $perubahanStok = $this->perubahanStok($jenisTransaksi, $kondisi, $jumlah);

        return DB::transaction(function () use (
            $barangId,
            $jenisTransaksi,
            $kondisi,
            $jumlah,
            $user,
            $tanggal,
            $keterangan,
            $perubahanStok,
        ): RiwayatStok {
            $barang = Barang::query()
                ->lockForUpdate()
                ->findOrFail($barangId);

            foreach ($perubahanStok as $kolomStok => $perubahan) {
                if ($perubahan < 0 && $barang->{$kolomStok} < abs($perubahan)) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Stok '.$this->namaKondisi($kolomStok).' tidak mencukupi. Stok tersedia: '.$barang->{$kolomStok}.'.',
                    ]);
                }
            }

            foreach ($perubahanStok as $kolomStok => $perubahan) {
                if ($perubahan < 0) {
                    $barang->decrement($kolomStok, abs($perubahan));

                    continue;
                }

                $barang->increment($kolomStok, $perubahan);
            }

            return RiwayatStok::query()->create([
                'barang_id' => $barang->id,
                'user_id' => $user->id,
                'jenis_transaksi' => $jenisTransaksi,
                'kondisi' => $kondisi,
                'jumlah' => $jumlah,
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
            ]);
        });
    }

    /**
     * @return array<string, int>
     */
    private function perubahanStok(string $jenisTransaksi, string $kondisi, int $jumlah): array
    {
        $kolomKondisi = match ($kondisi) {
            'baik' => 'stok_baik',
            'rusak_ringan' => 'stok_rusak_ringan',
            'rusak' => 'stok_rusak',
            default => throw new InvalidArgumentException('Kondisi stok tidak valid.'),
        };

        return match ($jenisTransaksi) {
            'masuk' => [$kolomKondisi => $jumlah],
            'keluar' => [$kolomKondisi => -$jumlah],
            'peminjaman' => $kondisi === 'baik'
                ? ['stok_baik' => -$jumlah, 'stok_dipinjam' => $jumlah]
                : throw new InvalidArgumentException('Barang yang dipinjam harus dalam kondisi baik.'),
            'pengembalian' => ['stok_dipinjam' => -$jumlah, $kolomKondisi => $jumlah],
            default => throw new InvalidArgumentException('Jenis transaksi stok tidak valid.'),
        };
    }

    private function namaKondisi(string $kolomStok): string
    {
        return match ($kolomStok) {
            'stok_baik' => 'barang baik',
            'stok_rusak_ringan' => 'barang rusak ringan',
            'stok_rusak' => 'barang rusak',
            'stok_dipinjam' => 'barang yang dipinjam',
            default => throw new InvalidArgumentException('Kolom stok tidak valid.'),
        };
    }
}

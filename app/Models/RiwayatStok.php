<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStok extends Model
{
    protected $table = 'riwayat_stok';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
        ];
    }

    // Relasi ke tabel Barang (barang_id)
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    // Relasi ke tabel Users/Pengguna (user_id)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

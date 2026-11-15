<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    protected $table = 'peminjaman';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'datetime',
            'tanggal_kembali' => 'datetime',
        ];
    }

    // Relasi: Peminjaman dengan barang (barang_id)
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    // Relasi: Peminjaman oleh user (user_id)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengembali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_pengembali_id');
    }
}

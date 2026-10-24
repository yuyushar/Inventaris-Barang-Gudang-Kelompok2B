<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    protected $table = 'peminjaman';
    protected $guarded = ['id'];

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
}
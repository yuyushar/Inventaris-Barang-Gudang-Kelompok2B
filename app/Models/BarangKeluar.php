<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    protected $table = 'barang_keluar';
    protected $guarded = ['id'];

    // Relasi ke tabel Barang (barang_id)
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    // Relasi ke tabel Users/Pengguna (user_id)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
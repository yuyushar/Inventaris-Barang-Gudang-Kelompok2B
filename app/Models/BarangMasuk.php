<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk'; 
    protected $guarded = ['id'];

    // Relasi ke tabel Supplier (supplier_id)
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

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
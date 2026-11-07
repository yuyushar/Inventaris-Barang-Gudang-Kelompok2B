<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $fillable = ['kode_barang', 'nama_barang', 'kategori_id', 'stok_baik', 'stok_rusak'];
    protected $guarded = ['id'];

    // Relasi: Barang kategori (Many to One)
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    // Relasi: Riwayat Stok (Many to One)
    public function riwayatStok()
    {
        return $this->hasMany(RiwayatStok::class);
    }

    // Relasi: Peminjaman (Many to One)
    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class);
    }
}
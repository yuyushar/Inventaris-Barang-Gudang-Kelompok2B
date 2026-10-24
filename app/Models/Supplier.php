<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';
    protected $guarded = ['id'];

    // Relasi ke tabel BarangMasuk (One to Many)
    public function barangMasuk()
    {
        return $this->hasMany(BarangMasuk::class);
    }
}
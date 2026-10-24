<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riwayat_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->enum('jenis_transaksi', ['masuk', 'keluar', 'penyesuaian', 'peminjaman', 'pengembalian']);
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak']);
            $table->unsignedInteger('jumlah');
            $table->string('gambar_kondisi')->nullable();
            $table->timestamp('tanggal')->useCurrent();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_stok');
    }
};

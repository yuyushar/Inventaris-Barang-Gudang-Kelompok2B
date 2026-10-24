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
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_peminjaman')->unique();
            $table->foreignId('barang_id')->constrained('barang')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('peminjam_nama');
            $table->unsignedInteger('jumlah');
            
            $table->timestamp('tanggal_pinjam')->useCurrent();
            $table->string('kondisi_saat_dipinjam');
            $table->string('gambar_bukti_pinjam')->nullable();
            
            $table->timestamp('tanggal_kembali')->nullable();
            $table->string('kondisi_saat_dikembalikan')->nullable();
            $table->string('gambar_bukti_kembali')->nullable();
            
            $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};

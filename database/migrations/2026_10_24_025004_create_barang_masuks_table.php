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
        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi');
            $table->timestamp('tanggal')->useCurrent();
            $table->foreignId('supplier_id')->constrained('supplier')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('barang_id')->constrained('barang')->restrictOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('jumlah');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_masuk');
    }
};

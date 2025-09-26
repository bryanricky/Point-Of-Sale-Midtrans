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
        Schema::create('pembayaran_pembeliansupplier', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedInteger('id_pembelian'); // Relasi ke tabel penjualan
            $table->unsignedBigInteger('bayar')->default(0); // Jumlah diterima
            $table->enum('status', ['pending', 'success', 'failed'])->nullable()->default(null); // Status pembayaran
            $table->string('snap_token', 60)->nullable(); // Token untuk Midtrans atau gateway pembayaran lainnya
            $table->enum('metode_pembayaran', ['transfer', 'cash'])->nullable()->default(null); // Metode pembayaran, sekarang bisa null
            $table->enum('status_pembayaran', ['hutang', 'tunai', 'nonhutang'])->default('hutang'); // Status pembayaran, dengan default 'hutang'
            $table->timestamps(); // Menyimpan waktu created_at dan updated_at

            // Relasi ke tabel penjualan
            $table->foreign('id_pembelian')->references('id_pembelian')->on('pembelian')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_pembeliansupplier');
    }
};

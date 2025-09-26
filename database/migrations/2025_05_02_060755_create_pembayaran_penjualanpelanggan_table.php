<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pembayaran_penjualanpelanggan', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedInteger('id_penjualan'); // Relasi ke tabel penjualan
            $table->unsignedBigInteger('jumlah_bayar')->default(0); // Jumlah diterima
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending'); // Status pembayaran
            $table->string('snap_token', 60)->nullable(); // Token untuk Midtrans atau gateway pembayaran lainnya
            $table->enum('metode_pembayaran', ['transfer', 'tunai'])->nullable()->default(null); // Metode pembayaran, sekarang bisa null
            $table->enum('status_pembayaran', ['hutang', 'lunas'])->default('hutang'); // Status pembayaran, dengan default 'hutang'
            $table->timestamps(); // Menyimpan waktu created_at dan updated_at

            // Relasi ke tabel penjualan
            $table->foreign('id_penjualan')->references('id_penjualan')->on('penjualankredit')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pembayaran_penjualanpelanggan');
    }
};

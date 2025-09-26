<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaranPenjualanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembayaran_penjualan', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedInteger('id_penjualan'); // Relasi ke tabel penjualan
            $table->unsignedBigInteger('diterima')->default(0); // Jumlah diterima
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending'); // Status pembayaran
            $table->string('snap_token', 60)->nullable(); // Token untuk Midtrans atau gateway pembayaran lainnya
            $table->enum('metode_pembayaran', ['transfer', 'non_transfer'])->default('non_transfer'); // Metode pembayaran
            $table->timestamps(); // Menyimpan waktu created_at dan updated_at

            // Relasi ke tabel penjualan
            $table->foreign('id_penjualan')->references('id_penjualan')->on('penjualan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pembayaran_penjualan');
    }
}

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
        Schema::create('pembayaranpelanggan', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_penjualan');
            $table->unsignedInteger('id_pelanggan')->nullable();
            $table->integer('total_item');
            $table->integer('total_harga');
            $table->tinyInteger('diskon')->default(0);
            $table->integer('harga_diskon')->nullable();
            $table->integer('bayar')->default(0);
            $table->integer('sisa_hutang')->default(0);
            $table->date('jatuh_tempo')->nullable();
            $table->date('tanggal_pembayaran');
            $table->enum('status', ['Cicilan', 'Lunas', 'Pending']);
            $table->enum('status_pembayaran', ['hutang', 'nonhutang']);
            $table->timestamps();

            // Foreign key (jika ada relasi ke tabel pembelian)
            $table->foreign('id_penjualan')->references('id_penjualan')->on('penjualankredit')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaranpelanggan');
    }
};

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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_pembelian');
            $table->unsignedInteger('id_supplier')->nullable();
            $table->integer('total_item');
            $table->integer('total_harga');
            $table->tinyInteger('diskon')->default(0);
            $table->integer('harga_diskon')->nullable();
            $table->integer('bayar')->default(0);
            $table->bigInteger('jumlah_bayar')->nullable();
            $table->bigInteger('kembalian')->nullable();
            $table->integer('sisa_hutang')->default(0);
            $table->date('jatuh_tempo')->nullable();
            $table->date('tanggal_pembayaran');
            $table->enum('status', ['Cicilan', 'Lunas', 'Pending']);
            $table->enum('status_pembayaran', ['hutang', 'nonhutang']);
            $table->timestamps();

            // Foreign key (jika ada relasi ke tabel pembelian)
            $table->foreign('id_pembelian')->references('id_pembelian')->on('pembelian')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};

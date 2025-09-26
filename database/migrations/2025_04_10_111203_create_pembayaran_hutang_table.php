<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaranHutangTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembayaran_hutang', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_pembelian'); // sesuaikan dengan primary key dari tabel pembelian
            $table->date('tanggal_pembayaran');
            $table->integer('diskon')->default(0);
            $table->integer('harga_diskon')->default(0);
            $table->integer('total_harga')->default(0);
            $table->integer('sudah_dibayar')->default(0);
            $table->unsignedBigInteger('jumlah_bayar');
            $table->integer('dibayarkan')->default(0);
            $table->bigInteger('kembalian')->default(0);
            $table->integer('sisa_hutang')->default(0);
            $table->string('snap_token', 60)->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending'); // Status pembayaran
            $table->timestamps();

            $table->foreign('id_pembelian')
                  ->references('id_pembelian')
                  ->on('pembelian')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_hutang');
    }
}

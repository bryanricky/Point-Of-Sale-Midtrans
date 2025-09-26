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
        Schema::create('penjualankredit', function (Blueprint $table) {
            $table->increments('id_penjualan');
            $table->integer('id_pelanggan');
            $table->integer('total_item');
            $table->integer('total_harga');
            $table->tinyInteger('diskon')->default(0);
            $table->integer('harga_diskon')->nullable();
            $table->unsignedBigInteger('bayar')->default(0);
            $table->integer('sisa_hutang')->default(0);
            $table->string('status_pembayaran', 20)->nullable();
            $table->date('jatuh_tempo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualankredit');
    }
};

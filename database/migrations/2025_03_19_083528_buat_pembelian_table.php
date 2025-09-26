<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuatPembelianTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->increments('id_pembelian');
            $table->integer('id_supplier');
            $table->integer('total_item');
            $table->tinyInteger('diskon')->default(0);
            $table->integer('harga_diskon')->nullable();
            $table->integer('total_harga');
            $table->unsignedBigInteger('bayar')->default(0);
            $table->integer('sisa_hutang')->default(0);
            $table->date('jatuh_tempo')->nullable();
            $table->string('status_pembayaran', 20)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};

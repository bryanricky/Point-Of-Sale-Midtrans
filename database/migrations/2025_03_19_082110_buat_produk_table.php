<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuatProdukTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        schema::create('produk', function (Blueprint $table) {
            $table->increments('id_produk');
            $table->string('kode_produk', 10)->unique();
            $table->string('id_kategori');
            $table->string('nama_produk', 50)->unique();
            $table->string('merk', 20)->nullable();
            $table->integer('harga_beli');
            $table->tinyInteger('diskon')->default(0);
            $table->integer('harga_jual');
            $table->integer('stok');
            $table->string('path_foto', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuatKategoriTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        schema::create('kategori', function (Blueprint $table) {
            $table->increments('id_kategori');
            $table->string('nama_kategori', 30)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        schema::dropIfExists('kategori');
    }
};

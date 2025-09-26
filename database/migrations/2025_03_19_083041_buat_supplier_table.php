<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuatSupplierTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        schema::create('supplier', function (Blueprint $table) {
            $table->increments('id_supplier');
            $table->string('nama', 30);
            $table->text('alamat')->nullable();
            $table->string('telepon', 15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        schema::dropIfExists('supplier');
    }
};

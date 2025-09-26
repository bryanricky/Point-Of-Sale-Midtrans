<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuatMemberTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        schema::create('member', function (Blueprint $table) {
            $table->increments('id_member');
            $table->string('kode_member', 10)->unique();
            $table->string('nama', 30);
            $table->string('alamat', 50)->nullable();
            $table->string('telepon', 15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        schema::dropIfExists('member');
    }
};

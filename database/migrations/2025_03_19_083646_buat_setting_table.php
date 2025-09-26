<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuatSettingTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('setting', function (Blueprint $table) {
            $table->increments('id_setting');
            $table->string('nama_perusahaan', 30);
            $table->text('alamat')->nullable();
            $table->string('telepon', 15);
            $table->tinyInteger('tipe_nota');
            $table->smallInteger('diskon')->default(0);
            $table->string('path_logo', 100);
            $table->string('path_kartu_member', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting');
    }
};

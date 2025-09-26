<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = [
            ['nama_kategori' => 'Beras'],
            ['nama_kategori' => 'Minyak Goreng'],
            ['nama_kategori' => 'Gula Pasir'],
            ['nama_kategori' => 'Tepung Terigu'],
            ['nama_kategori' => 'Telur Ayam'],
            ['nama_kategori' => 'Mie Instan'],
            ['nama_kategori' => 'Susu Kental Manis'],
            ['nama_kategori' => 'Sabun Mandi'],
            ['nama_kategori' => 'Deterjen'],
            ['nama_kategori' => 'Air Mineral'],
        ];

        DB::table('kategori')->insert($kategori);
    }
}

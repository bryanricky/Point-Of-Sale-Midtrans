<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            ['nama' => 'PT Sembako Jaya', 'alamat' => 'Jl. Industri No. 1, Surabaya', 'telepon' => '081234567001'],
            ['nama' => 'CV Makmur Sentosa', 'alamat' => 'Jl. Raya Pasar Besar, Malang', 'telepon' => '081234567002'],
            ['nama' => 'PT Pangan Sejahtera', 'alamat' => 'Jl. Panjaitan No. 5, Jakarta', 'telepon' => '081234567003'],
            ['nama' => 'CV Tani Subur', 'alamat' => 'Jl. Pertanian No. 7, Kediri', 'telepon' => '081234567004'],
            ['nama' => 'PT Maju Terus', 'alamat' => 'Jl. Merdeka No. 12, Bandung', 'telepon' => '081234567005'],
            ['nama' => 'CV Indo Berkah', 'alamat' => 'Jl. Kemakmuran No. 8, Solo', 'telepon' => '081234567006'],
            ['nama' => 'PT Beras Nasional', 'alamat' => 'Jl. Padi No. 10, Madiun', 'telepon' => '081234567007'],
            ['nama' => 'CV Minyak Kita', 'alamat' => 'Jl. Sawit No. 15, Yogyakarta', 'telepon' => '081234567008'],
            ['nama' => 'PT Sumber Pangan', 'alamat' => 'Jl. Pasar Induk No. 20, Semarang', 'telepon' => '081234567009'],
            ['nama' => 'CV Grosir Murah', 'alamat' => 'Jl. Niaga No. 18, Bekasi', 'telepon' => '081234567010'],
        ];

        foreach ($suppliers as $s) {
            DB::table('supplier')->insert([
                'nama' => $s['nama'],
                'alamat' => $s['alamat'],
                'telepon' => $s['telepon'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

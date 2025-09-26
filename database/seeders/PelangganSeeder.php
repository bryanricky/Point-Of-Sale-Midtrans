<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pelanggan = [
            ['nama' => 'Akbar', 'alamat' => 'Jl. Melati No. 1', 'telepon' => '081234567891'],
            ['nama' => 'Shendy', 'alamat' => 'Jl. Kenanga No. 2', 'telepon' => '081234567892'],
            ['nama' => 'Andhika', 'alamat' => 'Jl. Mawar No. 3', 'telepon' => '081234567893'],
            ['nama' => 'Dimas', 'alamat' => 'Jl. Flamboyan No. 4', 'telepon' => '081234567894'],
            ['nama' => 'Valliant', 'alamat' => 'Jl. Anggrek No. 5', 'telepon' => '081234567895'],
            ['nama' => 'Dheo', 'alamat' => 'Jl. Dahlia No. 6', 'telepon' => '081234567896'],
            ['nama' => 'Fahrezi', 'alamat' => 'Jl. Teratai No. 7', 'telepon' => '081234567897'],
            ['nama' => 'Ilman', 'alamat' => 'Jl. Cempaka No. 8', 'telepon' => '081234567898'],
            ['nama' => 'Bagas', 'alamat' => 'Jl. Sakura No. 9', 'telepon' => '081234567899'],
            ['nama' => 'Risandi', 'alamat' => 'Jl. Kamboja No. 10', 'telepon' => '081234567800'],
        ];

        foreach ($pelanggan as $p) {
            DB::table('pelanggan')->insert([
                'nama' => $p['nama'],
                'alamat' => $p['alamat'],
                'telepon' => $p['telepon'],
                'path_foto' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

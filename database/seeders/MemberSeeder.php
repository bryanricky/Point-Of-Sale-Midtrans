<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('member')->insert([
            [
                'kode_member' => 'MBR001',
                'nama' => 'Andi Saputra',
                'alamat' => 'Jl. Mawar No. 1',
                'telepon' => '081234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_member' => 'MBR002',
                'nama' => 'Budi Santoso',
                'alamat' => 'Jl. Melati No. 2',
                'telepon' => '081234567891',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_member' => 'MBR003',
                'nama' => 'Citra Dewi',
                'alamat' => 'Jl. Kenanga No. 3',
                'telepon' => '081234567892',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_member' => 'MBR004',
                'nama' => 'Dedi Kurniawan',
                'alamat' => 'Jl. Anggrek No. 4',
                'telepon' => '081234567893',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_member' => 'MBR005',
                'nama' => 'Erna Lestari',
                'alamat' => 'Jl. Dahlia No. 5',
                'telepon' => '081234567894',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User; // <== INI WAJIB ADA
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'ridho@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('ridho123'),
                'level' => 1
            ]
        );

        // User biasa
        User::updateOrCreate(
            ['email' => 'bryan@gmail.com'],
            [
                'name' => 'Kasir Bryan',
                'password' => Hash::make('bryan123'),
                'level' => 2 // Ubah sesuai struktur level di aplikasi Anda
            ]
        );

        // Pemilik toko
        User::updateOrCreate(
            ['email' => 'andhika@gmail.com'],
            [
                'name' => 'Kasir Andhika',
                'password' => Hash::make('andhika123'),
                'level' => 2
            ]
        );
    }
}

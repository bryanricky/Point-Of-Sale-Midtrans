<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Produk;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriProduk = [
            [1, 'Beras Ramos 5kg', 'Cap Ayam', 55000, 60000, 100],
            [2, 'Minyak Goreng 1L', 'Tropical', 15000, 17000, 120],
            [3, 'Gula Pasir 1kg', 'Gulavit', 13000, 15000, 80],
            [4, 'Tepung Terigu Segitiga Biru', 'Bogasari', 11000, 13000, 90],
            [5, 'Telur Ayam 1kg', null, 26000, 28000, 60],
            [6, 'Mie Instan Goreng', 'Indomie', 2500, 3000, 300],
            [7, 'Susu Kental Manis Putih', 'Indomilk', 9000, 11000, 70],
            [8, 'Sabun Mandi Lifebuoy', 'Lifebuoy', 5000, 6000, 100],
            [9, 'Deterjen Rinso 1kg', 'Rinso', 12000, 14000, 85],
            [10, 'Air Mineral 600ml', 'Aqua', 3000, 4000, 200],
        ];

        $id_terakhir = Produk::latest('id_produk')->value('id_produk') ?? 0;

        foreach ($kategoriProduk as $i => $data) {
            $kode_produk = 'P' . tambah_nol_didepan($id_terakhir + $i + 1, 6);

            DB::table('produk')->insert([
                'kode_produk' => $kode_produk,
                'id_kategori' => $data[0],
                'nama_produk' => $data[1],
                'merk' => $data[2],
                'harga_beli' => $data[3],
                'diskon' => 0,
                'harga_jual' => $data[4],
                'stok' => $data[5],
                'path_foto' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

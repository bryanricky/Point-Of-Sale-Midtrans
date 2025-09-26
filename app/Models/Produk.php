<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    protected $guarded = [];

    public function getFotoUrlAttribute()
{
    // Jika path_foto sudah berisi nama file saja
    return asset('storage/produk/' . $this->path_foto);

    // Jika file Anda di public/images, gunakan ini:
    // return asset('images/' . $this->path_foto);
}

}

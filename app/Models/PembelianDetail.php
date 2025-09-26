<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    use HasFactory;

    protected $table = 'pembelian_detail';
    protected $primaryKey = 'id_pembelian_detail';
    protected $guarded = [];
    protected $fillable = [
        'total_item', 'total_harga', 'diskon', 'harga_diskon', 'bayar', 'sisa_hutang', 'jatuh_tempo', 'jumlah_bayar', 'snap_token', 'path_foto'// dan lainnya
    ];

    public function produk()
    {
        return $this->hasOne(Produk::class, 'id_produk', 'id_produk');
    }
}

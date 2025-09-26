<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetailKredit extends Model
{
    use HasFactory;

    protected $table = 'penjualan_detailkredit';
    protected $primaryKey = 'id_penjualan_detailkredit';
    protected $guarded = [];
    protected $fillable = [
        'total_item', 'total_harga', 'diskon', 'harga_diskon', 'bayar', 'sisa_hutang', 'jatuh_tempo', 'jumlah_bayar', 'snap_token'// dan lainnya
    ];

    public function produk()
    {
        return $this->hasOne(Produk::class, 'id_produk', 'id_produk');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'id_pembelian',
        'id_supplier',
        'jumlah_bayar',
        'kembalian',
        'tanggal_pembayaran',
        'status',
        'status_pembayaran',
        'total_item', 
        'total_harga', 
        'diskon', 
        'harga_diskon', 
        'bayar', 
        'sisa_hutang', 
        'jatuh_tempo',
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }
}

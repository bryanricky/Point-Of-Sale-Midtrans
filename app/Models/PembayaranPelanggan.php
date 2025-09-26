<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranPelanggan extends Model
{
    protected $table = 'pembayaranpelanggan';

    protected $fillable = [
        'id_penjualan',
        'id_pelanggan',
        'jumlah_bayar',
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

    public function penjualanKredit()
    {
        return $this->belongsTo(PenjualanKredit::class, 'id_penjualan');
    }
}

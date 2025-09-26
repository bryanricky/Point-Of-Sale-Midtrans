<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranHutangPelanggan extends Model
{
    protected $table = 'pembayaran_hutangpelanggan';

    protected $fillable = [
        'id_penjualan',
        'tanggal_pembayaran',
        'jumlah_bayar',
        'kembalian',
        'status',
        'snap_token',
        'total_harga',
        'bayar',
        'sisa_hutang',
        'diskon',
        'harga_diskon',
        'dibayarkan'
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'datetime',
    ];
    

    public function penjualanKredit()
    {
        return $this->belongsTo(PenjualanKredit::class, 'id_penjualan');
    }
}
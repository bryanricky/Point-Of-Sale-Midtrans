<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranHutang extends Model
{
    protected $table = 'pembayaran_hutang';

    protected $fillable = [
        'id_pembelian', // ✅ ini disesuaikan
        'tanggal_pembayaran',
        'jumlah_bayar',
        'kembalian',
        'status',
        'snap_token',
        'total_harga',
        'sudah_dibayar',
        'sisa_hutang',
        'diskon',
        'harga_diskon',
        'dibayarkan'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian');
    }
}

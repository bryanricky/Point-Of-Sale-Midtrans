<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPenjualanPelanggan extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak sesuai dengan nama default (plural dari nama model)
    protected $table = 'pembayaran_penjualanpelanggan';

    // Tentukan kolom yang dapat diisi (mass assignable)
    protected $fillable = [
        'id_penjualan',
        'jumlah_bayar',
        'status',
        'snap_token',
        'metode_pembayaran',
        'status_pembayaran',
    ];

    // Relasi: PembayaranPenjualan belongs to Penjualan (One to Many)
    public function penjualanKredit()
    {
        return $this->belongsTo(PenjualanKredit::class, 'id_penjualan');
    }
}

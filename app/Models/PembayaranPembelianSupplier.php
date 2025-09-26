<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPembelianSupplier extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak sesuai dengan nama default (plural dari nama model)
    protected $table = 'pembayaran_pembeliansupplier';

    // Tentukan kolom yang dapat diisi (mass assignable)
    protected $fillable = [
        'id_pembelian',
        'bayar',
        'status',
        'snap_token',
        'metode_pembayaran',
        'status_pembayaran',
    ];

    // Relasi: PembayaranPenjualan belongs to Penjualan (One to Many)
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian');
    }
}

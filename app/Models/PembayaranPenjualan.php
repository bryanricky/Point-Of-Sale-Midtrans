<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPenjualan extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak sesuai dengan nama default (plural dari nama model)
    protected $table = 'pembayaran_penjualan';

    // Tentukan kolom yang dapat diisi (mass assignable)
    protected $fillable = [
        'id_penjualan',
        'diterima',
        'status',
        'metode_pembayaran',
    ];

    // Relasi: PembayaranPenjualan belongs to Penjualan (One to Many)
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }
}

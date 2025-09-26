<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';
    protected $guarded = [];
    protected $fillable = [
        'total_item', 'total_harga', 'diskon', 'harga_diskon', 'bayar', 'sisa_hutang', 'jatuh_tempo', 'jumlah_bayar', 'snap_token', 'order_id', 'status', 'status_pembayaran'// dan lainnya
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }

    public function pembayaranHutang()
    {
        return $this->hasMany(PembayaranHutang::class, 'id_pembelian');
    }
    
    public function getSisaHutangAttribute()
    {
        $hargaAkhir = $this->harga_diskon ?? $this->total_harga;
        return max(0, $hargaAkhir - $this->bayar);
    }
    public function pembelianDetail()
    {
        return $this->hasMany(PembelianDetail::class, 'id_pembelian', 'id_pembelian');
    }

    public function pembayaranPembelianSupplier()
    {
        return $this->hasMany(PembayaranPembelianSupplier::class, 'id_pembelian');
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembelian');
    }

    // Di model Pembelian.php
    public function pembelian_detail()
    {
        return $this->hasMany(PembelianDetail::class, 'id_pembelian');
    }

    // Model Pembelian.php
    public function detail()
    {
        return $this->hasMany(PembelianDetail::class, 'id_pembelian');
    }

}

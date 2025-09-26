<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanKredit extends Model
{
    use HasFactory;

    protected $table = 'penjualankredit';
    protected $primaryKey = 'id_penjualan';
    protected $guarded = [];
    protected $fillable = [
        'total_item', 'total_harga', 'diskon', 'harga_diskon', 'bayar', 'sisa_hutang', 'jatuh_tempo', 'jumlah_bayar', 'snap_token', 'status', 'status_pembayaran'// dan lainnya
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function pembayaranHutangPelanggan()
    {
        return $this->hasMany(PembayaranHutangPelanggan::class, 'id_penjualan');
    }

    public function pembayaranPenjualanPelanggan()
    {
        return $this->hasMany(PembayaranPenjualanPelanggan::class, 'id_penjualan');
    }
    
    public function getSisaHutangAttribute()
    {
        $hargaAkhir = $this->harga_diskon ?? $this->total_harga;
        return max(0, $hargaAkhir - $this->bayar);
    }
    public function penjualanDetailKredit()
    {
        return $this->hasMany(PenjualanDetailKredit::class, 'id_penjualan', 'id_penjualan');
    }

    // app/Models/PenjualanKredit.php

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_pembelian');
    }

    public function penjualan_detailkredit()
    {
        return $this->hasMany(PenjualanDetailKredit::class, 'id_penjualan');
    }

    // Model Pembelian.php
    public function detail()
    {
        return $this->hasMany(PenjualanDetailKredit::class, 'id_penjualan');
    }
}
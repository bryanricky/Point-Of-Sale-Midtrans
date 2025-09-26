<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    protected $primaryKey = 'id_penjualan';
    protected $guarded = [];
    // Tentukan kolom yang dapat diisi (mass assignable)
    protected $fillable = [
        'id_penjualan',
        'diterima',
        'status',
        'metode_pembayaran',
    ];

    public function member()
    {
        return $this->hasOne(Member::class, 'id_member', 'id_member');
    }

    public function pelanggan()
{
    return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
}


    public function user()
    {
        return $this->hasOne(User::class, 'id', 'id_user');
    }

    // Relasi: Penjualan memiliki banyak PembayaranPenjualan
    public function pembayaranPenjualan()
    {
        return $this->hasMany(PembayaranPenjualan::class, 'id_penjualan'); // Pastikan foreign key 'penjualan_id'
    }
}

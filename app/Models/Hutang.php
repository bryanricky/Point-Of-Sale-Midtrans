<?php

// app/Models/Hutang.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hutang extends Model
{
    protected $table = 'hutang'; // atau 'hutangs' tergantung nama tabel
    protected $fillable = [
        'id_supplier',
        'id_pelanggan',
        'total_hutang',
        'sisa_hutang',
        'status_pembayaran', // contoh: 'belum lunas', 'lunas'
        'keterangan',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function pelunasan()
    {
        return $this->hasMany(PelunasanHutang::class);
    }
}

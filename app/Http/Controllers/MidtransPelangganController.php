<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenjualanKredit;
use App\Models\PembayaranPenjualanPelanggan;

class MidtransPelangganController extends Controller
{
    public function paymentForm($snap_token)
    {
       
        return view('midtranspelanggan.payment', compact('snap_token'));
    }

    public function pembayaranSuccess()
{
    $id_penjualan = session('id_penjualan');

    if ($id_penjualan) {
        $pembayaran = PembayaranPenjualanPelanggan::where('id_penjualan', $id_penjualan)->first();
        if ($pembayaran && $pembayaran->status === 'pending') {
            $pembayaran->update(['status' => 'success']);
        }

        return view('midtranspelanggan.success', compact('id_penjualan'));
    }

    return redirect()->route('penjualankredit.index')->with('error', 'Pembayaran tidak valid.');
}


}

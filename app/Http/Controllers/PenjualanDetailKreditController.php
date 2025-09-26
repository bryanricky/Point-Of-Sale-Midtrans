<?php

namespace App\Http\Controllers;

use App\Models\PenjualanKredit;
use App\Models\PenjualanDetailKredit;
use App\Models\Produk;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PenjualanDetailKreditController extends Controller
{
    public function index()
    {
        $id_penjualan = session('id_penjualan');
        $produk = Produk::orderBy('nama_produk')->get();
        $pelanggan = Pelanggan::find(session('id_pelanggan'));
        $penjualankredit = PenjualanKredit::find($id_penjualan);
        $diskon = PenjualanKredit::find($id_penjualan)->diskon ?? 0;
        $status_pembayaran = $penjualankredit->status_pembayaran;

        if (! $pelanggan) {
            abort(404);
        }

        $status_class = match($status_pembayaran) {
            'hutang' => 'text-white fw-bold shadow-sm',
            'tunai'  => 'text-white fw-bold shadow-sm',
            'lunas'  => 'text-white fw-bold shadow-sm',
            default  => 'text-white fw-bold shadow-sm'
        };

        $status_style = match($status_pembayaran) {
            'hutang' => 'background-color: #dc3545;',  // merah terang
            'tunai'  => 'background-color: #007bff;',  // biru terang
            'lunas'  => 'background-color: #28a745;',  // hijau terang
            default  => 'background-color: #6c757d;'   // abu
        };
        
        // Hitung harga diskon
        $harga_diskon = $penjualankredit->total_harga - ($penjualankredit->total_harga * $diskon / 100);
        
        return view('penjualan_detailkredit.index', compact('id_penjualan', 'produk', 'pelanggan', 'diskon', 'status_pembayaran', 'harga_diskon' , 'status_class', 'status_style'));
    }

    public function data($id)
    {
        $detail = PenjualanDetailKredit::with('produk')
            ->where('id_penjualan', $id)
            ->get();
        $data = array();
        $total = 0;
        $total_item = 0;

        foreach ($detail as $item) {
            $row = array();
            $row['kode_produk'] = '<span class="label label-success">'. $item->produk['kode_produk'] .'</span';
            $row['nama_produk'] = $item->produk['nama_produk'];
            $row['merk'] = $item->produk['merk'];
            $row['harga_jual']  = 'Rp. '. format_uang($item->harga_jual);
            $row['jumlah'] = '<input type="number" class="form-control input-sm quantity input-jumlah" 
                data-id="'. $item->id_penjualan_detailkredit .'" 
                value="'. $item->jumlah .'" 
                max="'. $item->produk->stok .'" 
                min="1">';
            $row['subtotal']    = 'Rp. '. format_uang($item->subtotal);
            $row['aksi']        = '<div class="btn-group">
                                    <button onclick="deleteData(`'. route('penjualan_detailkredit.destroy', $item->id_penjualan_detailkredit) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                </div>';
            $data[] = $row;

            $total += $item->harga_jual * $item->jumlah;
            $total_item += $item->jumlah;
        }
        $data[] = [
            'kode_produk' => '
                <div class="total hide">'. $total .'</div>
                <div class="total_item hide">'. $total_item .'</div>',
            'nama_produk' => '',
            'merk' => '',
            'harga_jual'  => '',
            'jumlah'      => '',
            'subtotal'    => '',
            'aksi'        => '',
        ];

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->rawColumns(['aksi', 'kode_produk', 'jumlah'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $produk = Produk::where('id_produk', $request->id_produk)->first();
        if (! $produk) {
            return response()->json('Data gagal disimpan', 400);
        }

        $detail = new PenjualanDetailKredit();
        $detail->id_penjualan = $request->id_penjualan;
        $detail->id_produk = $produk->id_produk;
        $detail->harga_jual = $produk->harga_jual;
        $detail->jumlah = 1;
        $detail->subtotal = $produk->harga_jual;
        $detail->save();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function update(Request $request, $id)
    {
        $detail = PenjualanDetailKredit::find($id);
        $detail->jumlah = $request->jumlah;
        $detail->subtotal = $detail->harga_jual * $request->jumlah;
        $detail->update();
    }

    public function destroy($id)
    {
        $detail = PenjualanDetailKredit::find($id);
        $detail->delete();

        return response(null, 204);
    }

    public function loadForm($diskon, $total)
    {
        $bayar = $total - ($diskon / 100 * $total);
        $data  = [
            'totalrp' => format_uang($total),
            'bayar' => $bayar,
            'bayarrp' => format_uang($bayar),
            'terbilang' => ucwords(terbilang($bayar). ' Rupiah')
        ];

        return response()->json($data);
    }
    
}

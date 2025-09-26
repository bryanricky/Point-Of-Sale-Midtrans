<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Setting;
use Illuminate\Http\Request;
use PDF;
use App\Models\PembayaranPenjualan;
use Midtrans\Transaction;

class PenjualanController extends Controller
{
    public function index()
    {
        return view('penjualan.index');
    }

    public function data()
    {
        $penjualan = Penjualan::with('member')
            ->where('total_item', '>', 0)
            ->orderBy('id_penjualan', 'desc')
            ->get();

        return datatables()
            ->of($penjualan)
            ->addIndexColumn()
            ->addColumn('total_item', function ($penjualan) {
                return format_uang($penjualan->total_item);
            })
            ->addColumn('total_harga', function ($penjualan) {
                return 'Rp. '. format_uang($penjualan->total_harga);
            })
            ->addColumn('bayar', function ($penjualan) {
                return 'Rp. '. format_uang($penjualan->bayar);
            })
            ->addColumn('tanggal', function ($penjualan) {
                return tanggal_indonesia($penjualan->created_at, false);
            })
            ->addColumn('kode_member', function ($penjualan) {
                if ($penjualan->member && $penjualan->member->kode_member) {
                    return '<span class="label label-success">'. $penjualan->member->kode_member .'</span>';
                } else {
                    return '-';
                }
            })
            ->editColumn('diskon', function ($penjualan) {
                return $penjualan->diskon . '%';
            })
            ->editColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? '';
            })
            ->addColumn('aksi', function ($penjualan) {
                return '
                <div class="btn-group">
                    <button onclick="showDetail(`'. route('penjualan.show', $penjualan->id_penjualan) .'`)" class="btn btn-sm btn-info btn-flat"><i class="fa fa-eye"> Lihat</i></button>
                    <button onclick="deleteData(`'. route('penjualan.destroy', $penjualan->id_penjualan) .'`)" class="btn btn-sm btn-danger btn-flat"><i class="fa fa-trash"> Hapus</i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi', 'kode_member'])
            ->make(true);
    }

    public function create()
    {
        $penjualan = new Penjualan();
        $penjualan->id_member = null;
        $penjualan->total_item = 0;
        $penjualan->total_harga = 0;
        $penjualan->diskon = 0;
        $penjualan->bayar = 0;
        $penjualan->diterima = 0;
        $penjualan->id_user = auth()->id();
        $penjualan->save();

        session(['id_penjualan' => $penjualan->id_penjualan]);
        return redirect()->route('transaksi.index');
    }


    public function store(Request $request)
    {
        // Cari data penjualan berdasarkan ID
        $penjualan = Penjualan::findOrFail($request->id_penjualan);
        $penjualan->id_member = $request->id_member;
        $penjualan->total_item = $request->total_item;
        $penjualan->total_harga = $request->total;
        $penjualan->diskon = $request->diskon;
        $penjualan->bayar = $request->bayar;
        $penjualan->diterima = $request->diterima;
        $penjualan->update();

         // Cek stok untuk setiap produk
        $detail = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
        $produk = Produk::find($item->id_produk);
        
        // Cek apakah stok produk cukup
        if ($produk->stok < $item->jumlah) {
            return redirect()->back()->withErrors(['error' => 'stok' . $produk->nama_produk . ' tidak mencukupi.']);
        }

        // Update stok produk yang terjual
        $produk->stok -= $item->jumlah;
        $produk->update();

        $item->diskon = $request->diskon;
        $item->update();
    }

        // ===== Cek metode pembayaran =====
        if ($request->metode_pembayaran == 'transfer') {
            // Konfigurasi Midtrans untuk metode transfer
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => 'PENJUALAN-' . $penjualan->id_penjualan,
                    'gross_amount' => (int) $penjualan->diterima, // total diterima
                ],
                'customer_details' => [
                    'first_name' => 'Guest',
                    'email' => 'guest@example.com',
                ],
            ];

            // Mengambil Snap Token dari Midtrans
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Simpan ke tabel pembayaran_penjualan untuk metode transfer
            $pembayaran = new PembayaranPenjualan();
            $pembayaran->id_penjualan = $penjualan->id_penjualan;
            $pembayaran->snap_token = $snapToken;  // Simpan snap_token
            $pembayaran->diterima = $penjualan->diterima;
            $pembayaran->status = 'pending'; // Status pembayaran masih pending
            $pembayaran->metode_pembayaran = 'transfer'; // Simpan metode pembayaran transfer
            $pembayaran->save();

            // Redirect ke form baru untuk pembayaran transfer
            return redirect()->route('transaksi.pembayaranmidtrans', ['snap_token' => $snapToken]);

        } else {
            // Untuk metode pembayaran selain transfer (misal tunai)
            // Simpan ke tabel pembayaran_penjualan untuk metode non-transfer
            $pembayaran = new PembayaranPenjualan();
            $pembayaran->id_penjualan = $penjualan->id_penjualan;
            $pembayaran->snap_token = null;  // Tidak ada snap_token untuk non-transfer
            $pembayaran->diterima = $penjualan->diterima;
            $pembayaran->status = 'success'; // Status pembayaran masih pending
            $pembayaran->metode_pembayaran = 'non_transfer'; // Simpan metode pembayaran non-transfer
            $pembayaran->save();

            // Redirect ke halaman selesai untuk pembayaran non-transfer
            return redirect()->route('transaksi.selesai');
        }
    }

    public function pembayaranMidtrans($snap_token)
    {
        // Mengambil data pembayaran berdasarkan Snap Token yang diterima
        $pembayaran = PembayaranPenjualan::where('snap_token', $snap_token)->first();
         // Ambil id_penjualan dari pembayaran
        $id_penjualan = $pembayaran->id_penjualan;

        // Tampilkan view dengan Snap Token dan data pembayaran lainnya
        return view('transaksi.pembayaran-midtrans', compact('snap_token', 'id_penjualan'));
    }


    public function show($id)
    {
        $detail = PenjualanDetail::with('produk')->where('id_penjualan', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('kode_produk', function ($detail) {
                return '<span class="label label-success">'. $detail->produk->kode_produk .'</span>';
            })
            ->addColumn('nama_produk', function ($detail) {
                return $detail->produk->nama_produk;
            })
            ->addColumn('harga_jual', function ($detail) {
                return 'Rp. '. format_uang($detail->harga_jual);
            })
            ->addColumn('jumlah', function ($detail) {
                return format_uang($detail->jumlah);
            })
            ->addColumn('subtotal', function ($detail) {
                return 'Rp. '. format_uang($detail->subtotal);
            })
            ->rawColumns(['kode_produk'])
            ->make(true);
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        $detail    = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                $produk->stok += $item->jumlah;
                $produk->update();
            }

            $item->delete();
        }

        $penjualan->delete();

        return response(null, 204);
    }

    public function selesai(Request $request)
    {
        $id = $request->id; // ambil id_penjualan dari query parameter

        // Ambil pembayaran terakhir yang masih pending untuk pembelian ini
        $pembayaran = PembayaranPenjualan::where('id_penjualan', $id)
                            ->where('status', 'pending')
                            ->latest()
                            ->first();

        if ($pembayaran) {
            // Tandai status pembayaran jadi Success
            $pembayaran->status = 'success';
            $pembayaran->save();
        }

        $setting = Setting::first();

        return view('penjualan.selesai', compact('setting', 'id'));
    }

    public function notaKecil()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();
        
        return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail'));
    }

    public function notaBesar()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();

        $pdf = PDF::loadView('penjualan.nota_besar', compact('setting', 'penjualan', 'detail'));
        $pdf->setPaper(0,0,609,440, 'potrait');
        return $pdf->stream('Transaksi-'. date('Y-m-d-his') .'.pdf');
    }
}

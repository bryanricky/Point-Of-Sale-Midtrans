<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenjualanKredit;
use App\Models\PenjualanDetailKredit;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\PembayaranPenjualanPelanggan;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\PembayaranHutangPelanggan;
use App\Models\PembayaranPelanggan;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class PenjualanKreditController extends Controller
{
    public function index()
    {
        $pelanggan = Pelanggan::orderBy('nama')->get();

        return view('penjualankredit.index', compact('pelanggan'));
    }

    public function data()
    {

        $penjualankredit = PenjualanKredit::orderBy('id_penjualan', 'desc')->get();

        $penjualankredit = PenjualanKredit::whereNotNull('status_pembayaran') // Tambahkan ini
                          ->orderBy('id_penjualan', 'desc')
                          ->get();

        return datatables()
            ->of($penjualankredit)
            ->addIndexColumn()
            ->addColumn('total_item', function ($penjualankredit) {
                return format_uang($penjualankredit->total_item);
            })
            ->addColumn('total_harga', function ($penjualankredit) {
                return 'Rp. '. format_uang($penjualankredit->total_harga);
            })
            ->addColumn('bayar', function ($penjualankredit) {
                return 'Rp. '. format_uang($penjualankredit->bayar);
            })
            ->addColumn('sisa_hutang', function ($penjualankredit) {
                if ($penjualankredit->status_pembayaran === 'hutang') {
                    // Jika diskon 0 atau null, gunakan total_harga
                    $total = ($penjualankredit->diskon == 0 || is_null($penjualankredit->diskon)) 
                                ? $penjualankredit->total_harga 
                                : $penjualankredit->harga_diskon;
            
                    $sisa = $total - $penjualankredit->bayar;
                    $sisa = $sisa < 0 ? 0 : $sisa;
            
                    return 'Rp. '. format_uang($sisa);
                }
            
                return 'Rp. 0';
            })
            ->addColumn('harga_diskon', function ($item) {
                return 'Rp. '. format_uang($item->harga_diskon);
            })
            ->addColumn('status_pembayaran', function ($item) {
                return ucfirst($item->status_pembayaran);
            })
            ->addColumn('jatuh_tempo', function ($penjualankredit) {
                if (in_array($penjualankredit->status_pembayaran, ['hutang', 'lunas'])) {
                    return tanggal_indonesia($penjualankredit->jatuh_tempo, false);
                }
                return '-';
            })            
            ->addColumn('tanggal', function ($penjualankredit) {
                return tanggal_indonesia($penjualankredit->created_at, false);
            })
            ->addColumn('pelanggan', function ($penjualankredit) {
                return $penjualankredit->pelanggan->nama;
            })
            ->editColumn('diskon', function ($penjualankredit) {
                return $penjualankredit->diskon . '%';
            })
            ->addColumn('aksi', function ($penjualankredit) {
                return '
                <div class="btn-container">
                    <div class="btn-group">
                        <button type="button" onclick="showDetail(`'. route('penjualankredit.update', $penjualankredit->id_penjualan) .'`)" class="btn btn-xs btn-info btn-flat">
                            <i class="fa fa-pencil"></i> Lihat
                        </button>
                        <button type="button" onclick="deleteData(`'. route('penjualankredit.destroy', $penjualankredit->id_penjualan) .'`)" class="btn btn-xs btn-danger btn-flat">
                            <i class="fa fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create($id)
    {
        $penjualankredit = new PenjualanKredit();
        $penjualankredit->id_pelanggan = $id;
        $penjualankredit->total_item  = 0;
        $penjualankredit->total_harga = 0;
        $penjualankredit->diskon      = 0;
        $penjualankredit->bayar       = 0;
        $penjualankredit->save();

        session(['id_penjualan' => $penjualankredit->id_penjualan]);
        session(['id_pelanggan' => $penjualankredit->id_pelanggan]);

        return redirect()->route('penjualan_detailkredit.index');
    }

    public function createMidtransPayment($penjualankredit)
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $penjualankredit->id_penjualan,
                'gross_amount' => (int) $penjualankredit->bayar,
            ],
            'customer_details' => [
                'first_name' => $penjualankredit->pelanggan->nama ?? 'Pelanggan',
            ]
        ];

        return \Midtrans\Snap::getSnapToken($params); // <-- PASTIKAN INI RETURNNYA TOKEN BUKAN REDIRECT
    }

    public function store(Request $request)
    {
        $penjualankredit = PenjualanKredit::findOrFail($request->id_penjualan);

        // Ambil total dan diskon dari request
        $id_penjualan = $request->id_penjualan;
        $total = $request->total;
        $diskon = $request->diskon;
        $bayar = $request->bayar;
        $status_pembayaran = $request->status_pembayaran ?? 'hutang';
        // Hitung harga setelah diskon (dalam persen)
        $harga_diskon = $total - ($total * $diskon / 100);

        if ($request->status_pembayaran === 'hutang') {
            $batasBayar = $harga_diskon ?? $total;

            if ($bayar >= $batasBayar) {
                return redirect()->back()->with('error', 'Pembayaran hutang tidak boleh lebih dari total atau harga setelah diskon.');
            }
        }

        // Hitung harga setelah diskon hanya jika diskon > 0
        if ($diskon && $diskon > 0) {
            $harga_diskon = $total - ($total * $diskon / 100);
        } else {
            $harga_diskon = null; // atau bisa juga tetap diset ke $total jika diinginkan
        }

        // Simpan data pembelian
        $penjualankredit->total_item = $request->total_item;
        $penjualankredit->total_harga = $total;
        $penjualankredit->diskon = $diskon;
        $penjualankredit->harga_diskon = $harga_diskon;
        $penjualankredit->status_pembayaran = $request->status_pembayaran;
        $penjualankredit->bayar = $request->bayar;

        // Logika untuk status hutang atau lunas
        if ($request->status_pembayaran == 'hutang') {
            $penjualankredit->sisa_hutang = $request->sisa_hutang;
            $penjualankredit->jatuh_tempo = $request->jatuh_tempo;
        } else {
            $penjualankredit->sisa_hutang = 0;
            $penjualankredit->jatuh_tempo = null;
        }

        $penjualankredit->update();

        if ($request->status_pembayaran === 'hutang') {
            PembayaranPelanggan::updateOrCreate(
                [
                    'id_penjualan' => $penjualankredit->id_penjualan,
                ],
                [
                    'id_pelanggan' => $penjualankredit->id_pelanggan,
                    'bayar' => $bayar,
                    'total_item' => $penjualankredit->total_item,
                    'total_harga' => $penjualankredit->total_harga,
                    'diskon' => $penjualankredit->diskon,
                    'harga_diskon' => $penjualankredit->harga_diskon,
                    'sisa_hutang' => $penjualankredit->sisa_hutang,
                    'jatuh_tempo' => $penjualankredit->jatuh_tempo,
                    'tanggal_pembayaran' => now(),
                    'status' => 'Cicilan',
                    'status_pembayaran' => 'hutang',
                ]
            );
        }

        // KURANGI stok produk setelah pembayaran berhasil
        $detail = PenjualanDetailKredit::where('id_penjualan', $penjualankredit->id_penjualan)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                $produk->stok -= $item->jumlah;
                $produk->save();
            }
        }

        // Logika pembayaran
        if ($request->metode_pembayaran == 'transfer') {
            // Proses pembayaran dengan transfer, buat Snap Token untuk Midtrans
            $snapToken = $this->createMidtransPayment($penjualankredit);

            // Simpan pembayaran ke tabel pembayaran_penjualanpelanggan
            PembayaranPenjualanPelanggan::create([
                'id_penjualan' => $penjualankredit->id_penjualan,
                'jumlah_bayar' => $request->bayar,
                'status' => 'pending',
                'snap_token' => $snapToken,
                'metode_pembayaran' => 'transfer'
            ]);

            // Simpan id_penjualan ke session
            session(['id_penjualan' => $penjualankredit->id_penjualan]);

            // Redirect ke halaman pembayaran Midtrans
            return redirect()->route('midtranspelanggan.payment', ['snap_token' => $snapToken, 'id' => $penjualankredit->id_penjualan]);

        } elseif ($request->metode_pembayaran == 'tunai') {
            // Jika metode pembayaran tunai, langsung ubah status pembayaran menjadi success
            PembayaranPenjualanPelanggan::create([
                'id_penjualan' => $penjualankredit->id_penjualan,
                'jumlah_bayar' => $request->bayar,
                'status' => 'success',
                'snap_token' => null, // Tidak ada Snap Token jika tunai
                'metode_pembayaran' => 'tunai'
            ]);

            // Redirect ke halaman utama
            return redirect()->route('penjualankredit.pembayaran_tunai', ['id' => $penjualankredit->id_penjualan]);

        }
    }

    public function pembayaranTunai($id)
    {
        
        return view('penjualankredit.pembayaran_tunai', compact('id'));
    }

    public function show($id)
    {
        $detail = PenjualanDetailKredit::with('produk')->where('id_penjualan', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('kode_produk', function ($detail) {
                return '<span class="label label-success">'. $detail->produk->kode_produk .'</span>';
            })
            ->addColumn('nama_produk', function ($detail) {
                return $detail->produk->nama_produk;
            })
            ->addColumn('harga_beli', function ($detail) {
                return 'Rp. '. format_uang($detail->harga_beli);
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

    public function cetakStrukTunai($id)
    {
        // Ambil pembayaran terakhir yang sukses
        $pembayaranSekarang = PembayaranHutangPelanggan::where('id_penjualan', $id)
            ->where('status', 'success')
            ->latest('created_at')
            ->first();

        // Ambil data penjualan kredit
        $penjualankredit = PenjualanKredit::with('pelanggan')->find($id);

        $detail = $penjualankredit->penjualan_detailkredit; // <-- Tambahkan ini

        // Hitung ulang total bayar dan sisa hutang (tanpa bunga)
        $totalBayar = $penjualankredit->bayar ?? 0;
        $totalHarga = $penjualankredit->total_harga ?? 0;
        $diskon = $penjualankredit->diskon ?? 0;
        $hargaSetelahDiskon = $totalHarga - ($totalHarga * $diskon / 100);
        $sisaHutang = $hargaSetelahDiskon - $totalBayar;

        // Ambil pengaturan toko
        $setting = Setting::first(); // Pastikan kamu punya model Setting

        // Jika waktu pembayaran belum diset, fallback ke created_at
        if ($pembayaranSekarang && $pembayaranSekarang->tanggal_pembayaran->format('H:i') === '00:00') {
            $pembayaranSekarang->tanggal_pembayaran = $pembayaranSekarang->created_at;
        }

        return view('penjualankredit.struk_tunai', [
            'pembayaranSekarang' => $pembayaranSekarang,
            'penjualankredit' => $penjualankredit,
            'totalBayar' => $totalBayar,
            'sisaHutang' => max($sisaHutang, 0),
            'setting' => $setting,
            'detail' => $detail, // <-- Tambahkan ini
        ]);
    }


    public function destroy($id)
    {
        $penjualankredit = PenjualanKredit::find($id);
        $detail    = PenjualanDetailKredit::where('id_penjualan', $penjualankredit->id_penjualan)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                $produk->stok -= $item->jumlah;
                $produk->update();
            }
            $item->delete();
        }

        $penjualankredit->delete();

        return response(null, 204);
    }
}

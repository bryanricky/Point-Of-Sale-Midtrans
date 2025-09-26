<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Produk;
use App\Models\Supplier;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\PembayaranPembelianSupplier;
use App\Models\PembayaranHutang;
use App\Models\Pembayaran;
use App\Models\Setting;

class PembelianController extends Controller
{
    public function index()
    {
        $supplier = Supplier::orderBy('nama')->get();

        return view('pembelian.index', compact('supplier'));
    }

    public function data()
    {
        $pembelian = Pembelian::whereNotNull('status_pembayaran')
                          ->orderBy('id_pembelian', 'desc')
                          ->get();

        return datatables()
            ->of($pembelian)
            ->addIndexColumn()
            ->addColumn('total_item', function ($pembelian) {
                return format_uang($pembelian->total_item);
            })
            ->addColumn('total_harga', function ($pembelian) {
                return 'Rp. '. format_uang($pembelian->total_harga);
            })
            ->addColumn('bayar', function ($pembelian) {
                return 'Rp. '. format_uang($pembelian->bayar);
            }) 
            ->addColumn('sisa_hutang', function ($pembelian) {
                if ($pembelian->status_pembayaran === 'hutang') {
                    // Jika diskon 0 atau null, gunakan total_harga
                    $total = ($pembelian->diskon == 0 || is_null($pembelian->diskon)) 
                                ? $pembelian->total_harga 
                                : $pembelian->harga_diskon;
            
                    $sisa = $total - $pembelian->bayar;
            
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
            ->addColumn('jatuh_tempo', function ($pembelian) {
                if (in_array($pembelian->status_pembayaran, ['hutang', 'lunas'])) {
                    return tanggal_indonesia($pembelian->jatuh_tempo, false);
                }
                return '-';
            })
            ->addColumn('tanggal', function ($pembelian) {
                return tanggal_indonesia($pembelian->created_at, false);
            })
            ->addColumn('supplier', function ($pembelian) {
                return $pembelian->supplier->nama;
            })
            ->editColumn('diskon', function ($pembelian) {
                return $pembelian->diskon . '%';
            })
            ->addColumn('aksi', function ($pembelian) {
                return '
                <div class="btn-container">
                    <div class="btn-group">
                        <button type="button" onclick="showDetail(`'. route('pembelian.update', $pembelian->id_pembelian) .'`)" class="btn btn-xs btn-info btn-flat">
                            <i class="fa fa-pencil"></i> Lihat
                        </button>
                        <button type="button" onclick="deleteData(`'. route('pembelian.destroy', $pembelian->id_pembelian) .'`)" class="btn btn-xs btn-danger btn-flat">
                            <i class="fa fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
                ';
            })
            ->rawColumns(['aksi', 'status_pembayaran'])
            ->make(true);
    }

    public function create($id)
    {
        $pembelian = new Pembelian();
        $pembelian->id_supplier = $id;
        $pembelian->total_item  = 0;
        $pembelian->total_harga = 0;
        $pembelian->diskon      = 0;
        $pembelian->bayar       = 0;
        $pembelian->save();

        session(['id_pembelian' => $pembelian->id_pembelian]);
        session(['id_supplier' => $pembelian->id_supplier]);

        return redirect()->route('pembelian_detail.index');
    }

    public function __construct()
    {
        Config::$serverKey = 'SB-Mid-server-YKdjM55wTLpGrGtKjwvjdCct'; // Ganti dengan server key kamu
        Config::$isProduction = false; // true jika production
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function store(Request $request)
    {
        if ($request->metode_pembayaran == 'transfer') {
            $id_pembelian = $request->id_pembelian;
            $total = $request->total;
            $diskon = $request->diskon;
            $bayar = $request->bayar;
            $status_pembayaran = $request->status_pembayaran ?? 'hutang';
            $status = 'pending';

            // Hitung harga setelah diskon
            $harga_diskon = ($diskon && $diskon > 0) ? ($total - ($total * $diskon / 100)) : $total;

            // Jika statusnya hutang, bayar_akhir adalah jumlah yang dibayarkan
            if ($request->status_pembayaran === 'hutang') {
                $bayar_akhir = $bayar;
            } else {
                $bayar_akhir = $harga_diskon ?? $total;
            }

        // Validasi pembayaran nonhutang (tunai)
        if ($request->status_pembayaran === 'nonhutang') {
            if ($bayar < $bayar_akhir) {
                return redirect()->back()->with('error', 'Jumlah bayar tidak boleh kurang dari total pembayaran.');
            }

            $kembalian = $bayar - $bayar_akhir;
            session()->flash('kembalian', $kembalian);
        }

        
            if ($request->status_pembayaran === 'hutang') {
                if ($bayar >= $total || $bayar >= $harga_diskon) {
                    return redirect()->back()->with('error', 'Untuk pembayaran hutang, jumlah bayar harus kurang dari total harga atau harga setelah diskon.');
                }
            }

            // Generate snap_token Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => uniqid('INV-'),
                    'gross_amount' => (int) $bayar,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Simpan ke tabel pembayaran_pembelian_supplier
            PembayaranPembelianSupplier::create([
                'id_pembelian' => $id_pembelian,
                'bayar' => $bayar,
                'snap_token' => $snapToken,
                'status' => $status, // simpan sesuai logika
                'metode_pembayaran' => 'transfer',
                'status_pembayaran' => $status_pembayaran, // atau 'belum bayar'
            ]);
            
            $pembelian = Pembelian::findOrFail($request->id_pembelian);
            $total = $request->total;
            $diskon = $request->diskon;
            $harga_diskon = ($diskon && $diskon > 0) ? ($total - ($total * $diskon / 100)) : null;

            $pembelian->total_item = $request->total_item;
            $pembelian->total_harga = $total;
            $pembelian->diskon = $diskon;
            $pembelian->harga_diskon = $harga_diskon;
            $pembelian->status_pembayaran = $request->status_pembayaran;
            $pembelian->bayar = $bayar_akhir;

            if ($request->status_pembayaran == 'hutang') {
                $pembelian->sisa_hutang = $request->sisa_hutang;
                $pembelian->jatuh_tempo = $request->jatuh_tempo;
            } else {
                $pembelian->sisa_hutang = 0;
                $pembelian->jatuh_tempo = null;
            }

            $pembelian->update();

            Pembayaran::updateOrCreate(
                ['id_pembelian' => $pembelian->id_pembelian],
                [
                    'id_supplier'      => $pembelian->id_supplier,
                    'bayar'            => $bayar_akhir,
                    'jumlah_bayar'     => $bayar,
                    'kembalian'        => $request->status_pembayaran === 'nonhutang' ? $kembalian : null,
                    'total_item'       => $pembelian->total_item,
                    'total_harga'      => $pembelian->total_harga,
                    'diskon'           => $pembelian->diskon,
                    'harga_diskon'     => $pembelian->harga_diskon,
                    'sisa_hutang'      => $pembelian->sisa_hutang,
                    'jatuh_tempo'      => $pembelian->jatuh_tempo,
                    'tanggal_pembayaran' => now(),
                    'status'           => $request->status_pembayaran === 'hutang' ? 'Cicilan' : 'Lunas',
                    'status_pembayaran'=> $request->status_pembayaran,
                ]
            );

            $detail = PembelianDetail::where('id_pembelian', $pembelian->id_pembelian)->get();
            foreach ($detail as $item) {
                $produk = Produk::find($item->id_produk);
                $produk->stok += $item->jumlah;
                $produk->update();
            }

            // Simpan snap_token ke session untuk view
            session(['snap_token' => $snapToken]);

            return redirect()->route('pembelian.midtrans', ['id' => $pembelian->id_pembelian]);
        }

        $total = $request->total;
        $diskon = $request->diskon;
        $bayar = $request->bayar;
        // Hitung harga setelah diskon
        $harga_diskon = ($diskon && $diskon > 0) ? ($total - ($total * $diskon / 100)) : $total;

        // Jika statusnya hutang, bayar_akhir adalah jumlah yang dibayarkan
        if ($request->status_pembayaran === 'hutang') {
            $bayar_akhir = $bayar;
        } else {
            $bayar_akhir = $harga_diskon ?? $total;
        }

        // Validasi pembayaran nonhutang (tunai)
        if ($request->status_pembayaran === 'nonhutang') {
            if ($bayar < $bayar_akhir) {
                return redirect()->back()->with('error', 'Jumlah bayar tidak boleh kurang dari total pembayaran.');
            }

            $kembalian = $bayar - $bayar_akhir;
            session()->flash('kembalian', $kembalian);
        }

        if ($request->status_pembayaran === 'hutang') {
            if ($bayar >= $total || $bayar >= $harga_diskon) {
                return redirect()->back()->with('error', 'Untuk pembayaran hutang, jumlah bayar harus kurang dari total harga atau harga setelah diskon.');
            }
        }

        // Metode pembayaran cash (langsung simpan)
        $pembelian = Pembelian::findOrFail($request->id_pembelian);
        $total = $request->total;
        $diskon = $request->diskon;
        $harga_diskon = ($diskon && $diskon > 0) ? ($total - ($total * $diskon / 100)) : null;
        $bayar = $request->bayar;

        $pembelian->total_item = $request->total_item;
        $pembelian->total_harga = $total;
        $pembelian->diskon = $diskon;
        $pembelian->harga_diskon = $harga_diskon;
        $pembelian->status_pembayaran = $request->status_pembayaran;
        $pembelian->bayar = $bayar_akhir;

        if ($request->status_pembayaran == 'hutang') {
            $pembelian->sisa_hutang = $request->sisa_hutang;
            $pembelian->jatuh_tempo = $request->jatuh_tempo;
        } else {
            $pembelian->sisa_hutang = 0;
            $pembelian->jatuh_tempo = null;
        }

        $pembelian->update();

        Pembayaran::updateOrCreate(
            ['id_pembelian' => $pembelian->id_pembelian],
            [
                'id_supplier'      => $pembelian->id_supplier,
                'bayar'            => $bayar_akhir,
                'jumlah_bayar'     => $bayar,
                'kembalian'        => $request->status_pembayaran === 'nonhutang' ? $kembalian : null,
                'total_item'       => $pembelian->total_item,
                'total_harga'      => $pembelian->total_harga,
                'diskon'           => $pembelian->diskon,
                'harga_diskon'     => $pembelian->harga_diskon,
                'sisa_hutang'      => $pembelian->sisa_hutang,
                'jatuh_tempo'      => $pembelian->jatuh_tempo,
                'tanggal_pembayaran' => now(),
                'status'           => $request->status_pembayaran === 'hutang' ? 'Cicilan' : 'Lunas',
                'status_pembayaran'=> $request->status_pembayaran,
            ]
        );

        $detail = PembelianDetail::where('id_pembelian', $pembelian->id_pembelian)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            $produk->stok += $item->jumlah;
            $produk->update();
        }

        return redirect()->route('pembelian.pembayaran_tunai', ['id' => $pembelian->id_pembelian]);
    }


    public function pembayaranTunai($id)
    {
        
        return view('pembelian.pembayaran_tunai', compact('id'));
    }

    public function cetakStrukTunai($id)
{
    $pembayaranSekarang = Pembayaran::where('id_pembelian', $id)
        ->latest('tanggal_pembayaran')
        ->first();

    $pembelian = Pembelian::with('supplier')->find($id);
    $detail = $pembelian->pembelian_detail;

    $totalBayar = $pembelian->bayar ?? 0;
    $totalHarga = $pembelian->total_harga ?? 0;
    $diskon = $pembelian->diskon ?? 0;
    $hargaSetelahDiskon = $totalHarga - ($totalHarga * $diskon / 100);
    $sisaHutang = $hargaSetelahDiskon - $totalBayar;

    $setting = Setting::first();

    return view('pembelian.struk_tunai', [
        'pembayaranSekarang' => $pembayaranSekarang,
        'pembelian' => $pembelian,
        'totalBayar' => $totalBayar,
        'sisaHutang' => max($sisaHutang, 0),
        'setting' => $setting,
        'detail' => $detail,
    ]);
}


    public function formMidtrans()
    {
        $snapToken = session('snap_token'); // Ambil dari session

        if (!$snapToken) {
            return redirect()->route('pembelian.index')->with('error', 'Snap token tidak ditemukan.');
        }

        return view('pembelian.midtrans', [
            'snap_token' => $snapToken
        ]);
    }

    public function pembayaranSuccess()
    {
        $id_pembelian = session('id_pembelian');
        // Optional: Ambil pembayaran terbaru berdasarkan user/login (kalau ada relasi)
        $lastPembayaran = PembayaranPembelianSupplier::where('status', 'pending')
            ->where('metode_pembayaran', 'transfer')
            ->latest()
            ->first();

        // Update status jadi success
        if ($lastPembayaran) {
            $lastPembayaran->update([
                'status' => 'success',
            ]);
        }

        return view('pembelian_detail.pembayaran-sukses', compact('id_pembelian'));
    }

    public function show($id)
    {
        $detail = PembelianDetail::with('produk')->where('id_pembelian', $id)->get();

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

    public function destroy($id)
    {
        $pembelian = Pembelian::find($id);
        $detail    = PembelianDetail::where('id_pembelian', $pembelian->id_pembelian)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                $produk->stok -= $item->jumlah;
                $produk->update();
            }
            $item->delete();
        }

        $pembelian->delete();

        return response(null, 204);
    }
}

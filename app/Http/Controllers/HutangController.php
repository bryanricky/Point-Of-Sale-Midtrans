<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembayaranHutang;
use Illuminate\Support\Facades\Validator;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Transaction;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use Carbon\Carbon;

class HutangController extends Controller
{
    public function index()
    {
        $dataHutang = Pembelian::with('supplier')
            ->where('status_pembayaran', 'hutang')
            ->whereColumn('bayar', '<', 'total_harga')
            ->orderByDesc('id_pembelian')
            ->get()
            ->map(function ($item) {
                $item->sisa_hutang = $item->total_harga - $item->bayar;

                $jatuhTempo = \Carbon\Carbon::parse($item->jatuh_tempo)->endOfDay(); // jadi jam 23:59:59
                $now = \Carbon\Carbon::now();

                if ($now->toDateString() <= $jatuhTempo->toDateString()) {

                    $diff = $now->diff($jatuhTempo);
                    $hari = $diff->d + ($diff->m * 30) + ($diff->y * 365); // tambahkan jika jatuh tempo beda bulan/tahun
                    $jam = $diff->h;

                    $peringatan = trim(($hari > 0 ? "{$hari} hari " : '') . ($jam > 0 ? "{$jam} jam" : ''));

                    if ($hari <= 5) {
                        $item->peringatan = "Segera bayar (Kurang {$peringatan} lagi)";
                    } else {
                        $item->peringatan = null;
                    }
                } else {
                    $diff = $jatuhTempo->diff($now);
                    $hari = $diff->d + ($diff->m * 30) + ($diff->y * 365);
                    $jam = $diff->h;

                    $peringatan = trim(($hari > 0 ? "{$hari} hari " : '') . ($jam > 0 ? "{$jam} jam" : ''));

                    $item->peringatan = "Telat bayar ({$peringatan})";
                }

                return $item;
            });

        return view('hutang.index', compact('dataHutang'));
    }

    public function pembayaran()
    {
        $dataLunas = Pembelian::with('supplier')
            ->where('status_pembayaran', 'lunas') // hanya ambil yang sudah lunas
            ->orderByDesc('id_pembelian') // urutkan dari terbaru ke lama
            ->get()
            ->map(function ($item) {
                $item->sisa_hutang = 0;
                return $item;
            });

        return view('hutang.pembayaran', compact('dataLunas'));
    }

    public function formLunas($id)
    {
        $pembelian = Pembelian::findOrFail($id);
        return view('hutang.lunas', compact('pembelian'));
    }

    // Tampilkan form pembayaran hutang
    public function formBayar($id)
    {
        $pembelian = Pembelian::with('supplier')->findOrFail($id);
        $sisaHutang = $pembelian->total_harga - $pembelian->bayar;
        $pembayaranList = PembayaranHutang::where('id_pembelian', $id)
            ->where('status', 'success') // Tambahkan filter hanya status sukses
            ->orderBy('tanggal_pembayaran', 'asc')
            ->paginate(5); // Menampilkan 5 data per halaman

        // Kirim data jatuh_tempo ke view
        $jatuhTempo = $pembelian->jatuh_tempo; // Asumsi jatuh_tempo ada dalam tabel pembelian

        return view('hutang.bayar', compact('pembelian', 'sisaHutang', 'pembayaranList', 'jatuhTempo'));
    }

    public function simpanBayar(Request $request, $id)
    {
        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1',
            'tanggal_pembayaran' => 'required|date',
        ]);

        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => 'PB-' . $request->id_pembelian . '-' . time(),
                'gross_amount' => $request->jumlah_bayar,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        // Simpan snap_token ke session
        session(['snap_token' => $snapToken]);
        session(['jumlah_bayar' => $request->jumlah_bayar]);

        $pembelian = Pembelian::findOrFail($id);

        // Hitung harga setelah diskon
        $totalHarga = $pembelian->total_harga;
        $diskon = $pembelian->diskon ?? 0;
        $hargaSetelahDiskon = $totalHarga - ($totalHarga * $diskon / 100);

        $sudahDibayar = $pembelian->bayar ?? 0;

        $jumlahBayar = $request->jumlah_bayar;
        $sisaHutang = $hargaSetelahDiskon - $sudahDibayar;

        // Logika dibayarkan dan kembalian
        if ($jumlahBayar >= $sisaHutang) {
            $dibayarkan = $sisaHutang;
            $kembalian = $jumlahBayar - $sisaHutang;
        } else {
            $dibayarkan = $jumlahBayar;
            $kembalian = 0;
        }

        $sisaHutangSetelahBayar = $sisaHutang - $dibayarkan;

        if ($request->metode_pembayaran == 'tunai') {
            // Simpan langsung dengan status Lunas
            PembayaranHutang::create([
                'id_pembelian' => $id,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
                'jumlah_bayar' => $jumlahBayar,
                'dibayarkan' => $dibayarkan,
                'kembalian' => $kembalian,
                'total_harga'    => $totalHarga,
                'sudah_dibayar'  => $sudahDibayar + $dibayarkan,
                'sisa_hutang'    => $sisaHutangSetelahBayar,
                'diskon'         => $diskon,
                'harga_diskon'   => $diskon > 0 ? $hargaSetelahDiskon : 0,
                'status' => 'success',
                'snap_token' => null,
            ]);
    
            // Update total bayar di pembelian
            $pembelian->bayar += $dibayarkan;
            $pembelian->save();
    
            return redirect()->route('hutang.pembayarantunai',$id)->with('success', 'Pembayaran tunai berhasil disimpan.');
        }

        // Simpan hanya ke pembayaran_hutang dengan status Pending
        $pembayaran = PembayaranHutang::create([
            'id_pembelian' => $id,
            'tanggal_pembayaran' => $request->tanggal_pembayaran,
            'jumlah_bayar' => $jumlahBayar,
            'dibayarkan' => 0,
            'kembalian' => $kembalian,
            'total_harga'    => $totalHarga,
            'sudah_dibayar'  => 0,
            'sisa_hutang'    => 0,
            'diskon'         => $diskon,
            'harga_diskon'   => $diskon > 0 ? $hargaSetelahDiskon : 0,
            'status' => 'Pending',
            'snap_token' => $snapToken,
        ]);
        
        return redirect()->route('hutang.formLunas', $id)->with([
            'success' => 'Pembayaran berhasil disimpan, silakan selesaikan transaksi.',
            'snap_token' => $snapToken,
        ]);
    }

    public function pembayaranSuccess(Request $request, $id)
{
    // Ambil data pembelian
    $pembelian = Pembelian::where('id_pembelian', $id)
        ->where('status_pembayaran', 'hutang')
        ->first();

    if ($pembelian) {
        // Ambil pembayaran terakhir yang masih pending
        $pembayaranPending = PembayaranHutang::where('id_pembelian', $id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($pembayaranPending) {
            $jumlahBayar = $pembayaranPending->jumlah_bayar;

            // Hitung harga setelah diskon
            $totalHarga = $pembelian->total_harga;
            $diskon = $pembelian->diskon ?? 0;
            $hargaSetelahDiskon = $totalHarga - ($totalHarga * $diskon / 100);

            $sudahDibayar = $pembelian->bayar ?? 0;
            $sisaHutang = $hargaSetelahDiskon - $sudahDibayar;

            // Logika dibayarkan dan kembalian
            if ($jumlahBayar >= $sisaHutang) {
                $dibayarkan = $sisaHutang;
                $kembalian = $jumlahBayar - $sisaHutang;
            } else {
                $dibayarkan = $jumlahBayar;
                $kembalian = 0;
            }

            $sudahDibayarBaru = $sudahDibayar + $dibayarkan;
            $sisaHutangSetelahBayar = $hargaSetelahDiskon - $sudahDibayarBaru;

            // Update pembelian
            $pembelian->bayar = $sudahDibayarBaru;
            $pembelian->sisa_hutang = max($sisaHutangSetelahBayar, 0);

            if ($pembelian->bayar >= $hargaSetelahDiskon) {
                $pembelian->status_pembayaran = 'lunas';
            }

            $pembelian->save();

            // Update pembayaran pending
            $pembayaranPending->update([
                'status' => 'success',
                'dibayarkan' => $dibayarkan,
                'kembalian' => $kembalian,
                'sudah_dibayar' => $sudahDibayarBaru,
                'sisa_hutang' => max($sisaHutangSetelahBayar, 0),
                'diskon' => $diskon,
                'harga_diskon' => $diskon > 0 ? $hargaSetelahDiskon : 0,
                'total_harga' => $totalHarga,
            ]);
        }
    }

    // Ambil pembayaran sukses terbaru
    $pembayaranSekarang = PembayaranHutang::where('id_pembelian', $id)
        ->where('status', 'success')
        ->latest('created_at')
        ->first();

    return view('hutang.success', [
        'id' => $id,
        'pembayaranSekarang' => $pembayaranSekarang,
        'tanggalPembayaran' => $pembayaranSekarang?->tanggal_pembayaran ?? '-',
    ]);
}


    public function pembayaranTunai($id)
    {
        // Ambil pembayaran terakhir yang masih pending untuk pembelian ini
        $pembayaran = Pembelian::where('id_pembelian', $id)
                        ->where('status_pembayaran', 'hutang')
                        ->latest()
                        ->first();

        if ($pembayaran) {
           
            // Update ke tabel pembelian
            $pembelian = Pembelian::find($id);
            if ($pembelian) {
                // Tambahkan jumlah_bayar ke total bayar
                $pembelian->bayar += $pembayaran->jumlah_bayar;

                // Hitung ulang harga setelah diskon
                $totalHarga = $pembelian->total_harga;
                $diskon = $pembelian->diskon ?? 0;
                $hargaSetelahDiskon = $totalHarga - ($totalHarga * $diskon / 100);

                // Hitung sisa hutang
                $sisaHutang = $hargaSetelahDiskon - $pembelian->bayar;
                $pembelian->sisa_hutang = max($sisaHutang, 0); // Jangan sampai minus

                // Jika sudah lunas, update status
                if ($pembelian->bayar >= $hargaSetelahDiskon) {
                    $pembelian->status_pembayaran = 'lunas';
                }

                $pembelian->save();
            }
        }

        return view('hutang.pembayaran_tunai_berhasil', ['id' => $id]);
    }

    public function cetakStrukTunai($id)
    {
        // Ambil pembayaran terakhir yang sukses
        $pembayaranSekarang = PembayaranHutang::where('id_pembelian', $id)
            ->where('status', 'Success')
            ->latest('created_at')
            ->first();

        // Ambil data penjualan kredit
        $pembelian = Pembelian::with('supplier')->find($id);

        $detail = $pembelian->pembelian_detail; // <-- Tambahkan ini

        // Hitung ulang total bayar dan sisa hutang (tanpa bunga)
        $totalBayar = $pembelian->bayar ?? 0;
        $totalHarga = $pembelian->total_harga ?? 0;
        $diskon = $pembelian->diskon ?? 0;
        $hargaSetelahDiskon = $totalHarga - ($totalHarga * $diskon / 100);
        $sisaHutang = $hargaSetelahDiskon - $totalBayar;

        // Ambil pengaturan toko
        $setting = Setting::first(); // Pastikan kamu punya model Setting

        if ($pembayaranSekarang) {
        $tanggal = Carbon::parse($pembayaranSekarang->tanggal_pembayaran);

        if ($tanggal->format('H:i') === '00:00') {
            $pembayaranSekarang->tanggal_pembayaran = $pembayaranSekarang->created_at;
        } else {
            $pembayaranSekarang->tanggal_pembayaran = $tanggal;
        }
    }

        return view('hutang.struk_tunai', [
            'pembayaranSekarang' => $pembayaranSekarang,
            'pembelian' => $pembelian,
            'totalBayar' => $totalBayar,
            'sisaHutang' => max($sisaHutang, 0),
            'setting' => $setting,
            'detail' => $detail, // <-- Tambahkan ini
        ]);
    }

    public function hapusBayar($id)
    {
        $pembayaran = PembayaranHutang::findOrFail($id);
        $pembelian = Pembelian::findOrFail($pembayaran->id_pembelian);

        // Hitung harga acuan: total_harga atau harga setelah diskon
        $totalHarga = $pembelian->total_harga;
        $diskon = $pembelian->diskon ?? 0;

        // Jika diskon berupa persen
        $hargaAcuan = $totalHarga - ($totalHarga * $diskon / 100);

        // Hitung bayar efektif = jumlah_bayar - kembalian
        $bayarEfektif = $pembayaran->jumlah_bayar - ($pembayaran->kembalian ?? 0);

        // Update nilai bayar dan sisa hutang
        $pembelian->bayar -= $bayarEfektif;

        if ($pembelian->bayar < $hargaAcuan) {
            $pembelian->status_pembayaran = 'hutang';
        }

        $pembelian->sisa_hutang = $hargaAcuan - $pembelian->bayar;
        $pembelian->save();

        // Hapus pembayaran
        $pembayaran->delete();

        return redirect()->route('hutang.bayar.form', $pembayaran->id_pembelian)->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function updateBayar(Request $request, $id)
    {
        $request->validate([
            'tanggal_pembayaran' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:1',
        ]);

        $pembayaran = PembayaranHutang::findOrFail($id);
        $pembelian = Pembelian::findOrFail($pembayaran->pembelian_id);

        $selisih = $request->jumlah_bayar - $pembayaran->jumlah_bayar;
        $sisaHutang = $pembelian->total_harga - $pembelian->bayar;

        if ($selisih > $sisaHutang) {
            return redirect()->back()->with('error', 'Jumlah bayar melebihi sisa hutang.');
        }

        // Update pembayaran
        $pembayaran->update([
            'tanggal_pembayaran' => $request->tanggal_pembayaran,
            'jumlah_bayar' => $request->jumlah_bayar,
        ]);

        // Update nilai bayar
        $pembelian->bayar += $selisih;
        $pembelian->save();

        return redirect()->route('hutang.bayar.form', $pembelian->id_pembelian)->with('success', 'Pembayaran berhasil diupdate.');
    }

    public function destroyBayar($id)
    {
        $pembayaran = PembayaranHutang::findOrFail($id);
        $pembelianId = $pembayaran->pembelian_id;

        $pembayaran->delete();

        $totalBayar = PembayaranHutang::where('pembelian_id', $pembelianId)->sum('jumlah_bayar');
        Pembelian::where('id_pembelian', $pembelianId)->update(['bayar' => $totalBayar]);

        return back()->with('success', 'Pembayaran berhasil dihapus.');
    }
}
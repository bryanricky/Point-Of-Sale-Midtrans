<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenjualanKredit;
use App\Models\PembayaranHutangPelanggan;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class HutangPelangganController extends Controller
{
    
    public function index() 
    {
        $hutang = PenjualanKredit::with('pelanggan')
            ->where('status_pembayaran', 'hutang') // hanya ambil data dengan status hutang
            ->whereColumn('bayar', '<', 'total_harga') // pastikan masih ada sisa hutang
            ->orderByDesc('id_penjualan')
            ->get()
            ->map(function ($item) {
                $totalHarga = $item->total_harga;
                $diskon = $item->diskon ?? 0;
                $sudahDibayar = $item->bayar;

                // Total harga setelah diskon
                $totalHargaAfterDiscount = $diskon > 0
                    ? $totalHarga - ($totalHarga * $diskon / 100)
                    : $totalHarga;

                // Sisa hutang setelah diskon dan pembayaran
                $sisaHutang = $totalHargaAfterDiscount - $sudahDibayar;
                $item->sisa_hutang = $sisaHutang;

                // Hitung bunga
                $jatuhTempo = \Carbon\Carbon::parse($item->jatuh_tempo)->endOfDay();
                $now = now();

                $bunga = 0;
                if ($now->greaterThan($jatuhTempo)) {
                    $diff = $jatuhTempo->diff($now);
                    $hari = $diff->d + ($diff->m * 30) + ($diff->y * 365); // estimasi hari terlambat
                    $jam = $diff->h;

                    $bungaPerHari = 0.02; // 2% per hari
                    $bunga = $sisaHutang * $bungaPerHari * max(1, $hari);

                    $lamaTerlambat = trim(($hari > 0 ? "{$hari} hari " : '') . ($jam > 0 ? "{$jam} jam" : ''));
                    $item->lama_terlambat = $lamaTerlambat;
                } else {
                    $item->lama_terlambat = null;
                }

                $item->bunga = $bunga;
                $item->sisa_dengan_bunga = $sisaHutang + $bunga;

                // Peringatan mendekati jatuh tempo (misal <= 5 hari)
                if ($now->lessThanOrEqualTo($jatuhTempo) && $now->diffInDays($jatuhTempo, false) <= 5) {
                    $diff = $now->diff($jatuhTempo);
                    $hari = $diff->d + ($diff->m * 30) + ($diff->y * 365);
                    $jam = $diff->h;

                    $lamaMenujuJatuhTempo = trim(($hari > 0 ? "{$hari} hari " : '') . ($jam > 0 ? "{$jam} jam" : ''));
                    $namaPelanggan = $item->pelanggan->nama ?? 'Pelanggan';
                    $item->peringatan_tagihan = "Jatuh tempo kurang: $lamaMenujuJatuhTempo";
                } else {
                    $item->peringatan_tagihan = null;
                }

                return $item;
            });

            // ✅ Tambahkan pengecekan apakah ada bunga
            $adaBunga = $hutang->contains(function ($item) {
                return $item->bunga > 0;
            });

        return view('hutang_pelanggan.index', compact('hutang','adaBunga'));
    }
    
    public function pembayaranlunas()
    {
        $dataLunas = PenjualanKredit::with('pelanggan')
            ->where('status_pembayaran', 'lunas') // hanya ambil yang sudah lunas
            ->orderByDesc('id_penjualan') // urutkan dari terbaru ke lama
            ->get()
            ->map(function ($item) {
                $item->sisa_hutang = 0;
                return $item;
            });

        return view('hutang_pelanggan.pembayaran_lunas', compact('dataLunas'));
    }

    public function formBayar($id)
    {
        $penjualankredit = PenjualanKredit::with('pelanggan')->findOrFail($id);

        $totalHarga = $penjualankredit->total_harga;
        $diskon = $penjualankredit->diskon ?? 0;
        $sudahDibayar = $penjualankredit->bayar;
    
        // Hitung total harga setelah diskon (jika ada)
        $totalHargaAfterDiscount = $diskon > 0
            ? $totalHarga - ($totalHarga * $diskon / 100)
            : $totalHarga;
    
        // Hitung sisa hutang dari totalHargaAfterDiscount
        $sisaHutang = $totalHargaAfterDiscount - $sudahDibayar;

        $pembayaranList = PembayaranHutangPelanggan::where('id_penjualan', $id)
            ->where('status', 'success')
            ->orderBy('tanggal_pembayaran', 'asc')
            ->paginate(5);

        $jatuhTempo = \Carbon\Carbon::parse($penjualankredit->jatuh_tempo);
        $isTerlambat = now()->greaterThan($jatuhTempo->endOfDay());

        $bunga = 0;
        $hariTerlambat = 0;
        $jamTerlambat = 0;
        $lamaTerlambat = '';

        if ($isTerlambat) {
            $diff = $jatuhTempo->endOfDay()->diff(now());
            $hariTerlambat = $diff->d + ($diff->m * 30) + ($diff->y * 365); // Jika ingin akurat, bisa pakai ->days
            $jamTerlambat = $diff->h;

            $lamaTerlambat = trim(($hariTerlambat > 0 ? "{$hariTerlambat} hari " : '') . ($jamTerlambat > 0 ? "{$jamTerlambat} jam" : ''));

            $bungaPerHari = 0.02; // 2% per hari
            $bunga = $sisaHutang * $bungaPerHari * max(1, $hariTerlambat); // tetap kena bunga meskipun telat beberapa jam
        }

        $sisaHutangDenganBunga = $sisaHutang + $bunga;

        return view('hutang_pelanggan.bayar', compact(
            'penjualankredit',
            'sisaHutang',
            'pembayaranList',
            'jatuhTempo',
            'bunga',
            'sisaHutangDenganBunga',
            'lamaTerlambat'
        ));
    }

    public function simpanBayar(Request $request, $id)
    {
        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1',
            'tanggal_pembayaran' => 'required|date',
        ]);

        // MIDTRANS CONFIG
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // Ambil data penjualan
        $penjualankredit = PenjualanKredit::findOrFail($id);

        $totalHarga = $penjualankredit->total_harga;
        $diskon = $penjualankredit->diskon ?? 0;
        $hargaSetelahDiskon = $totalHarga - ($totalHarga * $diskon / 100);
        $bunga = $this->hitungBunga($penjualankredit);

        $totalYangHarusDibayar = $hargaSetelahDiskon + $bunga;
        $sudahDibayar = $penjualankredit->bayar ?? 0;

        $sisaHutangDenganBunga = $totalYangHarusDibayar - $sudahDibayar;
        $sisaHutangTanpaBunga = $hargaSetelahDiskon - $sudahDibayar;

        $jumlahBayar = $request->jumlah_bayar;
        $kembalian = 0;

        // Logika kembalian sesuai ada/tidaknya bunga
        if ($bunga > 0) {
            if ($jumlahBayar > $sisaHutangDenganBunga) {
                $kembalian = $jumlahBayar - $sisaHutangDenganBunga;
            }
        } else {
            if ($jumlahBayar > $sisaHutangTanpaBunga) {
                $kembalian = $jumlahBayar - $sisaHutangTanpaBunga;
            }
        }

        // Tetap pakai sisa hutang dengan bunga untuk pembayaran
        $dibayarkan = min($jumlahBayar, $sisaHutangDenganBunga);
        $sisaHutangSetelahBayar = $sisaHutangDenganBunga - $dibayarkan;

        // MIDTRANS SNAP TOKEN
        $params = [
            'transaction_details' => [
                'order_id' => 'PB-' . $request->id_pembelian . '-' . time(),
                'gross_amount' => $jumlahBayar,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);
        session(['snap_token' => $snapToken]);
        session(['jumlah_bayar' => $jumlahBayar]);

        if ($request->metode_pembayaran == 'tunai') {
            PembayaranHutangPelanggan::create([
                'id_penjualan' => $id,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
                'jumlah_bayar' => $jumlahBayar,
                'dibayarkan' => $dibayarkan,
                'kembalian' => $kembalian,
                'total_harga'    => $totalHarga,
                'bayar'          => $sudahDibayar + $dibayarkan,
                'sisa_hutang'    => $sisaHutangSetelahBayar,
                'diskon'         => $diskon,
                'harga_diskon'   => $diskon > 0 ? $hargaSetelahDiskon : 0,
                'status' => 'success',
                'snap_token' => null,
            ]);

            $penjualankredit->bayar += $dibayarkan;
            $penjualankredit->save();

            return redirect()->route('hutang_pelanggan.pembayarantunai', $id)
                            ->with('success', 'Pembayaran tunai berhasil disimpan.');
        }

        // Jika non-tunai (transfer, midtrans, dll)
        PembayaranHutangPelanggan::create([
            'id_penjualan' => $id,
            'tanggal_pembayaran' => $request->tanggal_pembayaran,
            'jumlah_bayar' => $jumlahBayar,
            'dibayarkan' => 0,
            'kembalian' => $kembalian,
            'total_harga'    => $totalHarga,
            'bayar'          => 0,
            'sisa_hutang'    => 0,
            'diskon'         => $diskon,
            'harga_diskon'   => $diskon > 0 ? $hargaSetelahDiskon : 0,
            'status' => 'pending',
            'snap_token' => $snapToken,
        ]);

        return redirect()->route('hutang_pelanggan.formLunas', $id)->with([
            'snap_token' => $snapToken,
        ]);
    }

    public function pembayaranTunai($id)
    {
        // Ambil pembayaran terakhir yang masih pending untuk pembelian ini
        $pembayaran = PenjualanKredit::where('id_penjualan', $id)
                        ->where('status_pembayaran', 'hutang')
                        ->latest()
                        ->first();
        if ($pembayaran) {
            // Update ke tabel pembelian
            $penjualankredit = PenjualanKredit::find($id);
            if ($penjualankredit) {
                // Tambahkan jumlah_bayar ke total bayar
                $penjualankredit->bayar += $pembayaran->jumlah_bayar;

                // Hitung ulang harga setelah diskon
                $totalHarga = $penjualankredit->total_harga;
                $diskon = $penjualankredit->diskon ?? 0;
                $hargaSetelahDiskon = $totalHarga - ($totalHarga * $diskon / 100);

                // Hitung sisa hutang
                $bunga = $this->hitungBunga($penjualankredit);
                $sisaHutang = ($hargaSetelahDiskon - $penjualankredit->bayar) + $bunga;
                $penjualankredit->sisa_hutang = max($sisaHutang, 0);

                // Jika sudah lunas, update status
                if ($penjualankredit->bayar >= $hargaSetelahDiskon) {
                    $penjualankredit->status_pembayaran = 'lunas';
                }

                $penjualankredit->save();
            }
        }
        // Ambil pembayaran terbaru (yang barusan dilakukan)
        $pembayaranSekarang = PembayaranHutangPelanggan::where('id_penjualan', $id)
            ->where('status', 'success')
            ->latest('created_at')
            ->first();

        return view('hutang_pelanggan.pembayaran_tunai', [
            'id' => $id,
            'pembayaranSekarang' => $pembayaranSekarang,
        ]);
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

        // Hitung ulang total bayar dan sisa hutang
        $totalBayar = $penjualankredit->bayar ?? 0;
        $totalHarga = $penjualankredit->total_harga ?? 0;
        $diskon = $penjualankredit->diskon ?? 0;
        $bunga = $this->hitungBunga($penjualankredit); // jika kamu punya fungsi ini
        $hargaSetelahDiskon = $totalHarga - ($totalHarga * $diskon / 100);
        $sisaHutang = ($hargaSetelahDiskon - $totalBayar) + $bunga;

        // Ambil pengaturan toko
        $setting = Setting::first(); // Pastikan kamu punya model Setting

        // Tambahkan logika fallback ke created_at jika tidak ada waktu
        if ($pembayaranSekarang && $pembayaranSekarang->tanggal_pembayaran->format('H:i') === '00:00') {
            $pembayaranSekarang->tanggal_pembayaran = $pembayaranSekarang->created_at;
        }

        return view('hutang_pelanggan.struk_tunai', [
            'pembayaranSekarang' => $pembayaranSekarang,
            'penjualankredit' => $penjualankredit,
            'totalBayar' => $totalBayar,
            'sisaHutang' => max($sisaHutang, 0),
            'setting' => $setting,
            'detail' => $detail, // <-- Tambahkan ini

        ]);
    }

    public function formLunas($id)
    {
        $penjualankredit = PenjualanKredit::findOrFail($id);
        return view('hutang_pelanggan.pembayaran_midtrans', compact('penjualankredit'));
    }

    public function pembayaranSuccess(Request $request, $id)
    {
        // Ambil data penjualan kredit dengan status hutang
        $penjualankredit = PenjualanKredit::where('id_penjualan', $id)
                            ->where('status_pembayaran', 'hutang')
                            ->first();
        if ($penjualankredit) {
            // Ambil pembayaran terakhir yang masih pending
            $pembayaranPending = PembayaranHutangPelanggan::where('id_penjualan', $id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($pembayaranPending) {
                $jumlahBayar = $pembayaranPending->jumlah_bayar;

                // Hitung harga setelah diskon
                $totalHarga = $penjualankredit->total_harga;
                $diskon = $penjualankredit->diskon ?? 0;
                $hargaSetelahDiskon = $totalHarga - ($totalHarga * $diskon / 100);

                // Hitung bunga dan total yang harus dibayar
                $bunga = $this->hitungBunga($penjualankredit);
                $totalYangHarusDibayar = $hargaSetelahDiskon + $bunga;

                $sudahDibayar = $penjualankredit->bayar ?? 0;
                $sisaHutangDenganBunga = $totalYangHarusDibayar - $sudahDibayar;

                // Hitung nilai dibayarkan (real amount yang dihitung untuk pelunasan)
                $dibayarkan = min($jumlahBayar, $sisaHutangDenganBunga);

                // Update nilai di penjualan
                $penjualankredit->bayar += $dibayarkan;
                $sisaHutangBaru = $totalYangHarusDibayar - $penjualankredit->bayar;
                $penjualankredit->sisa_hutang = max($sisaHutangBaru, 0);

                if ($penjualankredit->sisa_hutang <= 0) {
                    $penjualankredit->status_pembayaran = 'lunas';
                }

                $penjualankredit->save();

                // Simpan informasi pembayaran di tabel pembayaran_hutangpelanggan
                $pembayaranPending->status = 'success';
                $pembayaranPending->bayar = $penjualankredit->bayar;
                $pembayaranPending->sisa_hutang = $penjualankredit->sisa_hutang;
                $pembayaranPending->dibayarkan = $dibayarkan;
                $pembayaranPending->save();
            }
        }

        // Ambil pembayaran terbaru yang berhasil
        $pembayaranSekarang = PembayaranHutangPelanggan::where('id_penjualan', $id)
            ->where('status', 'success')
            ->latest('created_at')
            ->first();

        return view('hutang_pelanggan.success', [
            'id' => $id,
            'pembayaranSekarang' => $pembayaranSekarang,
            'tanggalPembayaran' => $pembayaranSekarang?->tanggal_pembayaran ?? '-',
        ]);
    }

    private function hitungBunga($item)
    {
        $jatuhTempo = \Carbon\Carbon::parse($item->jatuh_tempo)->endOfDay();
        $now = now();

        $diskon = $item->diskon ?? 0;
        $hargaSetelahDiskon = $item->total_harga - ($item->total_harga * $diskon / 100);
        $sisaHutang = $hargaSetelahDiskon - ($item->bayar ?? 0);

        if ($now->greaterThan($jatuhTempo)) {
            $diff = $jatuhTempo->diff($now);
            $hariTerlambat = $diff->d + ($diff->m * 30) + ($diff->y * 365); // estimasi keterlambatan
            $bungaPerHari = 0.02; // 2% per hari
            return max(0, $sisaHutang) * $bungaPerHari * max(1, $hariTerlambat);
        }

        return 0;
    }

    public function hapusBayar($id)
    {
        $pembayaran = PembayaranHutangPelanggan::findOrFail($id);
        $penjualankredit = PenjualanKredit::findOrFail($pembayaran->id_penjualan);

        // Hitung harga acuan: total_harga atau harga setelah diskon
        $totalHarga = $penjualankredit->total_harga;
        $diskon = $penjualankredit->diskon ?? 0;

        $hargaAcuan = $totalHarga - ($totalHarga * $diskon / 100);

        // Hitung bayar efektif = jumlah_bayar - kembalian
        $bayarEfektif = $pembayaran->jumlah_bayar - ($pembayaran->kembalian ?? 0);

        // Kurangi bayar di penjualan kredit dengan bayar efektif
        $penjualankredit->bayar -= $bayarEfektif;

        if ($penjualankredit->bayar < $hargaAcuan) {
            $penjualankredit->status_pembayaran = 'hutang';
        }

        // Hitung ulang sisa hutang
        $penjualankredit->sisa_hutang = $hargaAcuan - $penjualankredit->bayar;
        $penjualankredit->save();

        // Hapus data pembayaran
        $pembayaran->delete();

        return redirect()->route('hutang_pelanggan.bayar.form', $pembayaran->id_penjualan)
                        ->with('success', 'Pembayaran berhasil dihapus.');
    }
}
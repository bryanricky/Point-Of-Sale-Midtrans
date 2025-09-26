<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Member;
use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\PembayaranHutang;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\PembayaranHutangPelanggan;
use Illuminate\Support\Facades\DB; // Pastikan ini ditambahkan di bagian atas
use App\Models\PenjualanDetail;
use App\Models\PenjualanDetailKredit;
use App\Models\PembayaranPelanggan;
use App\Models\Pembayaran;
use Carbon\Carbon;
use App\Models\Pelanggan;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $kategori = Kategori::count();
        $produk = Produk::count();
        $supplier = Supplier::count();
        $member = Member::count();
        $pelanggan = Pelanggan::count();

        // Simpan tanggal ke session jika ada input baru dari form
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            session([
                'tanggal_awal' => $request->input('tanggal_awal'),
                'tanggal_akhir' => $request->input('tanggal_akhir'),
            ]);
        }

        // Ambil tanggal dari session, jika tidak ada default ke bulan ini
        $tanggal_awal = session('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggal_akhir = session('tanggal_akhir', now()->toDateString());


        // Format tanggal untuk tampilan (misalnya: 1 Mei 2025)
        function formatTanggalIndo($tanggal) {
            $bulanInggris = [
                'January' => 'Januari',
                'February' => 'Februari',
                'March' => 'Maret',
                'April' => 'April',
                'May' => 'Mei',
                'June' => 'Juni',
                'July' => 'Juli',
                'August' => 'Agustus',
                'September' => 'September',
                'October' => 'Oktober',
                'November' => 'November',
                'December' => 'Desember'
            ];
        
            $tanggal_format = date('j F Y', strtotime($tanggal));
            foreach ($bulanInggris as $en => $id) {
                $tanggal_format = str_replace($en, $id, $tanggal_format);
            }
            return $tanggal_format;
        }
        
        $tanggal_awal_format = formatTanggalIndo($tanggal_awal);
        $tanggal_akhir_format = formatTanggalIndo($tanggal_akhir);

        $data_tanggal = array();
        $data_pendapatan = array();

        $tgl_awal = Carbon::parse($tanggal_awal)->startOfDay();
        $tgl_akhir = Carbon::parse($tanggal_akhir)->endOfDay();


        while (strtotime($tanggal_awal) <= strtotime($tanggal_akhir)) {
            // Menyimpan tanggal dalam bentuk angka tanggal (misalnya 1, 2, 3, dst)
            $data_tanggal[] = (int) substr($tanggal_awal, 8, 2);

            // Menghitung total pendapatan berdasarkan transaksi
            $total_penjualan = Penjualan::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('bayar');
            $total_pembayaranpelanggan = PembayaranPelanggan::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('bayar');
            $total_pembayaranHutangPelanggan = PembayaranHutangPelanggan::where('tanggal_pembayaran', $tanggal_awal)->sum('dibayarkan');
            $total_pembayaran = Pembayaran::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('bayar');
            $total_pembayaranHutang = PembayaranHutang::where('tanggal_pembayaran', $tanggal_awal)->sum('dibayarkan');       
            // $total_pengeluaran = Pengeluaran::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('nominal');

            // Menghitung pendapatan bersih
            $pendapatan = $total_penjualan + $total_pembayaranHutangPelanggan + $total_pembayaranpelanggan - $total_pembayaranHutang - $total_pembayaran;
            $data_pendapatan[] = $pendapatan;

            // Menambahkan hari untuk loop berikutnya
            $tanggal_awal = date('Y-m-d', strtotime("+1 day", strtotime($tanggal_awal)));
        }

        
        // Ambil 10 produk dengan penjualan terbanyak
        $produk_terlaris = DB::table(DB::raw("
            (
                SELECT produk.nama_produk, SUM(jumlah) as total_terjual
                FROM penjualan_detail
                JOIN produk ON penjualan_detail.id_produk = produk.id_produk
                WHERE penjualan_detail.created_at BETWEEN ? AND ?
                GROUP BY produk.nama_produk

                UNION ALL

                SELECT produk.nama_produk, SUM(jumlah) as total_terjual
                FROM penjualan_detailkredit
                JOIN produk ON penjualan_detailkredit.id_produk = produk.id_produk
                WHERE penjualan_detailkredit.created_at BETWEEN ? AND ?
                GROUP BY produk.nama_produk
            ) as combined
        "))
        ->select('nama_produk', DB::raw('SUM(total_terjual) as total_terjual'))
        ->groupBy('nama_produk')
        ->orderByDesc('total_terjual')
        ->setBindings([$tgl_awal, $tgl_akhir, $tgl_awal, $tgl_akhir])
        ->get();


        // Batasi jumlah yang ditampilkan di grafik
        $produk_terlaris_graph = $produk_terlaris->take(10);

        $total_produk_terjual = PenjualanDetail::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->sum('jumlah');
        $total_produk_kredit = PenjualanDetailKredit::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->sum('jumlah');

        $hari_ini = Carbon::today()->toDateString();

        $total_penjualan_hari_ini = Penjualan::whereDate('created_at', $hari_ini)->sum('bayar');
        $total_pembayaran_pelanggan_hari_ini = PembayaranPelanggan::whereDate('created_at', $hari_ini)->sum('bayar');
        $total_pembayaran_hutang_pelanggan_hari_ini = PembayaranHutangPelanggan::where('tanggal_pembayaran', $hari_ini)->sum('jumlah_bayar');
        $total_pembayaran_hari_ini = Pembayaran::whereDate('created_at', $hari_ini)->sum('bayar');
        $total_pembayaran_hutang_hari_ini = PembayaranHutang::where('tanggal_pembayaran', $hari_ini)->sum('jumlah_bayar');
        // $total_pengeluaran_hari_ini = Pengeluaran::whereDate('created_at', $hari_ini)->sum('nominal');

        // Pendapatan bersih hari ini
        $pendapatan_hari_ini = $total_penjualan_hari_ini + $total_pembayaran_pelanggan_hari_ini + $total_pembayaran_hutang_pelanggan_hari_ini
                                - $total_pembayaran_hari_ini - $total_pembayaran_hutang_hari_ini;

       
        // Ambil data pelanggan berhutang dan tambahkan kolom peringatan_tagihan
        $pelanggan_berhutang = DB::table('penjualankredit')
            ->join('pelanggan', 'penjualankredit.id_pelanggan', '=', 'pelanggan.id_pelanggan')
            ->select(
                'pelanggan.nama as nama_pelanggan',
                'pelanggan.telepon',
                'penjualankredit.jatuh_tempo',
                DB::raw('SUM(penjualankredit.sisa_hutang) as sisa_hutang')
            )
            ->where('penjualankredit.sisa_hutang', '>', 0)
            ->groupBy('pelanggan.id_pelanggan', 'pelanggan.nama', 'pelanggan.telepon', 'penjualankredit.jatuh_tempo')
            ->get()
            ->map(function ($item) {
            $now = Carbon::now();
            $jatuhTempo = Carbon::parse($item->jatuh_tempo)->endOfDay();

            $selisihDetik = $now->diffInSeconds($jatuhTempo, false);

            if ($selisihDetik > 0) {
                $selisihJam = floor($selisihDetik / 3600);
                $hari = floor($selisihJam / 24);
                $jam = $selisihJam % 24;

                if ($hari < 5) { // hanya tampilkan jika kurang dari 5 hari
                    if ($hari > 0) {
                        $item->peringatan_tagihan = "Jatuh tempo kurang {$hari} hari";
                        if ($jam > 0) {
                            $item->peringatan_tagihan .= " {$jam} jam";
                        }
                    } elseif ($jam > 0) {
                        $item->peringatan_tagihan = "Jatuh tempo kurang {$jam} jam";
                    } else {
                        $item->peringatan_tagihan = null;
                    }
                } else {
                    $item->peringatan_tagihan = null;
                }

            } elseif ($selisihDetik < 0) {
                $selisihJam = floor(abs($selisihDetik) / 3600);
                $hari = floor($selisihJam / 24);
                $jam = $selisihJam % 24;

                $item->peringatan_tagihan = "Terlambat bayar";
                if ($hari > 0) {
                    $item->peringatan_tagihan .= " {$hari} hari";
                }
                if ($jam > 0) {
                    $item->peringatan_tagihan .= " {$jam} jam";
                }
            } else {
                $item->peringatan_tagihan = null;
            }

            return $item;
        });


        // Menampilkan data di view
        if (auth()->user()->level == 1) {
            return view('admin.dashboard', compact('kategori', 'produk', 'supplier', 'member', 'tanggal_awal_format', 
            'tanggal_akhir_format', 'data_tanggal', 'data_pendapatan', 'produk_terlaris', 'produk_terlaris_graph', 
            'total_produk_terjual', 'total_produk_kredit', 'pelanggan',  'pendapatan_hari_ini', 'pelanggan_berhutang'));
        } else {
            return view('kasir.dashboard', compact('tanggal_awal_format', 'tanggal_akhir_format', 'data_tanggal', 'data_pendapatan', 'produk_terlaris', 'produk_terlaris_graph'));
        }
    }
}


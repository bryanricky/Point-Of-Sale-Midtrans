<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\PembayaranHutang;
use App\Models\PembayaranHutangPelanggan;
use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\PembayaranPelanggan;
use App\Models\PenjualanKredit;
use App\Models\LogKasir;
use PDF;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');

        // Jika admin (level 1), izinkan ubah range tanggal
        if ($user->level == 1 && $request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('laporan.index', compact('tanggalAwal', 'tanggalAkhir'));
    }

    public function getData($awal, $akhir)
    {
        $user = auth()->user();

        if ($user->level == 2) {
            $awal = $akhir = date('Y-m-d'); // kasir hanya boleh lihat hari ini
        }

        $no = 1;
        $data = array();
        $total_pendapatan = 0;

        $awalAsli = $awal;

        while (strtotime($awal) <= strtotime($akhir)) {
            $tanggal = $awal;
            $awal = date('Y-m-d', strtotime("+1 day", strtotime($awal)));

            $total_penjualan = Penjualan::where('created_at', 'LIKE', "%$tanggal%")->sum('bayar');
            $total_pembayaranpelanggan = PembayaranPelanggan::where('created_at', 'LIKE', "%$tanggal%")->sum('bayar');
            $total_pembayaranHutangPelanggan = PembayaranHutangPelanggan::where('tanggal_pembayaran', $tanggal)->sum('dibayarkan');
            $total_pembayaran = Pembayaran::where('created_at', 'LIKE', "%$tanggal%")->sum('bayar');
            $total_pembayaranHutang = PembayaranHutang::where('tanggal_pembayaran', $tanggal)->sum('dibayarkan');       

            // Total kas_awal hari itu dari semua kasir (login tanggal itu)
            $kas_awal_total = LogKasir::whereDate('login_at', $tanggal)
                ->whereHas('user', function ($q) {
                    $q->where('level', 2);
                })
                ->sum('kas_awal');

            // Hitung pendapatan
            $pendapatan = $total_penjualan + $total_pembayaranpelanggan + $total_pembayaranHutangPelanggan - $total_pembayaranHutang - $total_pembayaran;
            $total_pendapatan += $pendapatan;

            // Simpan pendapatan bersih harian
            $pendapatan_bersih = $pendapatan - $kas_awal_total;

            $data[] = [
                'DT_RowIndex' => $no++,
                'tanggal' => tanggal_indonesia($tanggal, false),
                'penjualan' => format_uang($total_penjualan),
                'pembayaranpelanggan' => format_uang($total_pembayaranpelanggan),
                'pembayaran_hutangpelanggan' => format_uang($total_pembayaranHutangPelanggan),
                'pembayaran' => format_uang($total_pembayaran),
                'pembayaran_hutang' => format_uang($total_pembayaranHutang),
                'pendapatan' => number_format($pendapatan)
            ];

            // Tambahkan kas_awal hari itu (total semua kasir), tanpa tampilkan nama kasir
            $data[] = [
                'DT_RowIndex' => '',
                'tanggal' => '',
                'penjualan' => '',
                'pembayaranpelanggan' => '',
                'pembayaran_hutangpelanggan' => '',
                'pembayaran' => '',
                'pembayaran_hutang' => 'Modal Awal Kasir',
                'pendapatan' => format_uang($kas_awal_total)
            ];

            $data[] = [
                'DT_RowIndex' => '',
                'tanggal' => '',
                'penjualan' => '',
                'pembayaranpelanggan' => '',
                'pembayaran_hutangpelanggan' => '',
                'pembayaran' => '',
                'pembayaran_hutang' => 'Total Pendapatan harian',
                'pendapatan' => format_uang($pendapatan_bersih)
            ];
        }

        // Total kas_awal seluruh periode
        $total_kas_awal = LogKasir::whereDate('login_at', '>=', $awalAsli)
            ->whereDate('login_at', '<=', $akhir)
            ->whereHas('user', function ($q) {
                $q->where('level', 2);
            })
            ->sum('kas_awal');

        // Tampilkan total pendapatan keseluruhan hanya jika user adalah admin
        if (auth()->user()->level == 1) {
            $data[] = [
                'DT_RowIndex' => '',
                'tanggal' => '',
                'penjualan' => '',
                'pembayaranpelanggan' => '',
                'pembayaran_hutangpelanggan' => '',
                'pembayaran' => '',
                'pembayaran_hutang' => 'Total Semua Pendapatan',
                'pendapatan' => format_uang($total_pendapatan - $total_kas_awal),
            ];
        }

        return $data;
    }

    public function data($awal, $akhir)
    {
        $data = $this->getData($awal, $akhir);

        return datatables()
            ->of($data)
            ->make(true);
    }

    public function exportPDF($jenis, $awal, $akhir)
    {
        $user = auth()->user();

    if ($user->level == 2) {
        $awal = $akhir = date('Y-m-d'); // kasir hanya boleh cetak hari ini
    }

        $data = [];

        switch ($jenis) {
            case 'penjualan':
                $data = Penjualan::whereDate('created_at', '>=', $awal)
                    ->whereDate('created_at', '<=', $akhir)
                    ->get();
                $view = 'laporan.pdf.penjualan';
                $filename = "laporan-penjualan-$awal-$akhir.pdf";
                break;

            case 'pembayaran':
                $data = Pembayaran::whereDate('created_at', '>=', $awal)
                    ->whereDate('created_at', '<=', $akhir)
                    ->get();
                $view = 'laporan.pdf.pembelian';
                $filename = "laporan-pembelian-$awal-$akhir.pdf";
                break;

            case 'pengeluaran':
                $data = Pengeluaran::whereDate('created_at', '>=', $awal)
                    ->whereDate('created_at', '<=', $akhir)
                    ->get();
                $view = 'laporan.pdf.pengeluaran';
                $filename = "laporan-pengeluaran-$awal-$akhir.pdf";
                break;
            case 'pembayaranpelanggan':
                $data = PembayaranPelanggan::whereDate('created_at', '>=', $awal)
                    ->whereDate('created_at', '<=', $akhir)
                    ->get();
                $view = 'laporan.pdf.penjualankredit';
                $filename = "laporan-penjualankredit';-$awal-$akhir.pdf";
                break;
            case 'pembayaran_hutang':
                $data = PembayaranHutang::whereDate('created_at', '>=', $awal)
                    ->whereDate('created_at', '<=', $akhir)
                    ->get();
                $view = 'laporan.pdf.pembayaran_hutang';
                $filename = "laporan-pembayaran-hutang-$awal-$akhir.pdf";
                break;
            case 'pembayaran_hutangpelanggan':
                $data = PembayaranHutangPelanggan::whereDate('created_at', '>=', $awal)
                    ->whereDate('created_at', '<=', $akhir)
                    ->get();
                $view = 'laporan.pdf.pembayaran_piutang';
                $filename = "laporan-pembayaran-piutang-$awal-$akhir.pdf";
                break;

            default: // pendapatan
                $data = $this->getData($awal, $akhir);

                $view = 'laporan.pdf';
                $filename = "laporan-pendapatan-$awal-$akhir.pdf";
                break;
        }

        $pdf = PDF::loadView($view, compact('data', 'awal', 'akhir'));
        return $pdf->stream($filename);

    }

}

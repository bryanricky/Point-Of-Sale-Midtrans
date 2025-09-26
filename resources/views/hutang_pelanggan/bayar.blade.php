@extends('layouts.master')

@section('title')
    Form Pembayaran Piutang
@endsection

@section('content')
<style>
.btn-kembali {
    background-color: #6c757d; /* abu-abu */
    color: white;
}
/* Memperbesar ukuran font pada pagination */
.pagination li a, .pagination li span {
    font-size: 1.5rem !important; /* Ukuran font lebih besar */
    padding: 0.75rem 1.5rem !important; /* Menambahkan padding lebih besar */
}

.pagination .page-item.active .page-link {
    background-color: #007bff; /* Ubah background jika ingin */
    border-color: #007bff;
}

.table-gray {
    background-color: #f0f0f0; /* Warna abu-abu terang */
}

.table-gray th, .table-gray td {
    background-color: #e0e0e0; /* Warna abu-abu lebih gelap untuk isi tabel */
    border: 1px solid black;
}
</style>
<div class="row">
    <div class="col-lg-12">
        <div class="box-header with-border">
            <table style="font-size: 18px;">
            <tr>
                <td>Pelanggan</td>
                <td>: {{ $penjualankredit->pelanggan->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Telepon</td>
                <td>: {{ $penjualankredit->pelanggan->telepon ?? '-' }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $penjualankredit->pelanggan->alamat ?? '-' }}</td>
            </tr>
            </table>
        </div>
        <div class="box-body">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @php    
                $totalHarga = $penjualankredit->total_harga;
                $sudahDibayar = $penjualankredit->bayar;
                $diskon = $penjualankredit->diskon ?? 0; // Pastikan ada kolom diskon
                $totalHargaAfterDiscount = $totalHarga - ($totalHarga * $diskon / 100); // Menghitung total harga setelah diskon dalam persen
                $sisaHutang = max(0, $totalHargaAfterDiscount - $sudahDibayar);
                $listPembayaran = $penjualankredit->pembayaran_hutangpelanggan ?? []; // relasi hasMany di model Pembelian

            @endphp

            @if ($penjualankredit->status_pembayaran == 'lunas')
                <div class="alert alert-success mt-2" id="alert-lunas">
                    <span style="float: right; cursor: pointer;" onclick="document.getElementById('alert-lunas').style.display='none';">&times;</span>
                    Pembayaran sudah lunas, tidak dapat melakukan pembayaran lagi.
                </div>
            @elseif ($penjualankredit->status_pembayaran == 'hutang' && $sisaHutang > 0)
                <div class="alert alert-warning mt-2" id="alert-hutang">
                    <span style="float: right; cursor: pointer;" onclick="document.getElementById('alert-hutang').style.display='none';">&times;</span>
                    Masih terdapat sisa hutang, silakan lakukan pembayaran.
                </div>
            @endif

            <form action="{{ route('hutang_pelanggan.bayar.simpan', $penjualankredit->id_penjualan) }}" method="POST">
                @csrf
                <input type="hidden" id="sisa_hutang_dengan_bunga_raw" value="{{ $sisaHutangDenganBunga }}">
                <input type="hidden" id="sisa_hutang_raw" value="{{ $sisaHutang }}">

                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Total Harga</label>
                            <input type="text" class="form-control" id="total_harga" value="Rp {{ number_format($totalHarga) }}" disabled>
                        </div>

                        <div class="form-group">
                            <label>Diskon</label>
                            <input type="text" class="form-control" value="{{ number_format($diskon, 0, ',', '.') }}%" disabled>
                        </div>

                        @if($diskon > 0)
                            <div class="form-group">
                                <label>Total Harga Setelah Diskon</label>
                                <input type="text" class="form-control" id="harga_diskon" value="Rp {{ number_format($totalHargaAfterDiscount) }}" disabled>
                            </div>
                        @endif

                        <div class="form-group">
                            <label>Total Sudah Dibayar</label>
                            <input type="text" class="form-control" id="sudah_dibayar" value="Rp {{ number_format($sudahDibayar) }}" disabled>
                        </div>

                        <div class="form-group">
                            <label>Sisa Hutang</label>
                            <input type="text" class="form-control" id="sisa_hutang" value="Rp {{ number_format($sisaHutang) }}" readonly>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="tampil-bayar bg-primary text-white p-4 mb-3 rounded text-center" style="font-size: 4.5em; font-weight: bold; min-height: 100px;">
                                    <!-- Nilai total akan muncul di sini -->
                                </div>
                                <div class="tampil-terbilang bg-light text-dark p-3 rounded text-center" style="font-size: 2em; font-family: 'Arial', sans-serif; background-color: rgba(169, 169, 169, 0.4); min-height: 40px;">
                                    <!-- Terbilang total akan muncul di sini -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="col-md-6">
                        @if($bunga > 0)
                            <div class="form-group">
                                <label>Bunga Keterlambatan (2%/hari)</label>
                                <input type="text" class="form-control" readonly value="{{ 'Rp. ' . number_format($bunga, 0, ',', '.') }} (Telat {{ $lamaTerlambat }})">
                            </div>

                            <div class="form-group">
                                <label>Total yang Harus Dibayar</label>
                                <input type="text" class="form-control" id="sisa_hutang_dengan_bunga" readonly value="{{ 'Rp. ' . number_format($sisaHutangDenganBunga, 0, ',', '.') }}">
                            </div>
                        @endif
                       
                        @if ($sisaHutang > 0)
                            <div class="form-group">
                                <label for="metode_pembayaran">Metode Pembayaran</label>
                                <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                                    <option value="">-- Pilih Metode Pembayaran --</option>
                                    <option value="tunai">Tunai</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="tanggal_pembayaran">Tanggal Pembayaran</label>
                                <input type="date" name="tanggal_pembayaran" class="form-control" required>
                            </div>

                            <div class="form-group" id="form-jumlah-bayar">
                                <label for="jumlah_bayar">Jumlah Bayar</label>
                                <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control" min="0" value="0" placeholder="Masukkan nominal bayar" required>
                            </div>

                            <!-- Kembalian -->
                            <div class="form-group" id="form-kembalian" style="display: none;">
                                <label for="kembalian">Kembalian</label>
                                <input type="text" name="kembalian" id="kembalian" class="form-control" value="Rp 0" readonly>
                            </div>

                        @else
                            <div class="form-group">
                                <label for="tanggal_pembayaran">Tanggal Pembayaran</label>
                                <input type="date" name="tanggal_pembayaran" class="form-control" disabled>
                            </div>
                        @endif

                        @if ($sisaHutang > 0)
                            <button type="submit" class="btn btn-primary mt-2">Simpan Pembayaran</button>
                        @endif
                        <a href="{{ route('hutang_pelanggan.index') }}" class="btn btn-kembali mt-2">Kembali</a>
                    </div>
                </div>
            </form>

            <table class="table table-bordered" style="border-collapse: collapse; margin-top: 20px; text-align: center; table-layout: fixed; width: 100%;">
                <thead>
                    <tr>
                        <th style="border: 1px solid black; background-color: #cccccc; width: 16.6%; white-space: nowrap; text-align: center;">Tanggal Pembayaran</th>
                        <th style="border: 1px solid black; background-color: #cccccc; width: 16.6%; white-space: nowrap; text-align: center;">Total Harga Produk</th>
                        <th style="border: 1px solid black; background-color: #cccccc; width: 16.6%; white-space: nowrap; text-align: center;">Dibayarkan</th>
                        <th style="border: 1px solid black; background-color: #cccccc; width: 16.6%; white-space: nowrap; text-align: center;">Sisa Hutang</th>
                        <th style="border: 1px solid black; background-color: #cccccc; width: 16.6%; white-space: nowrap; text-align: center;">Total Sudah Dibayar</th>
                        <th style="border: 1px solid black; background-color: #cccccc; width: 16.6%; white-space: nowrap; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayaranList as $pembayaran)
                        <tr>
                            <td style="border: 1px solid black; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ \Carbon\Carbon::parse($pembayaran->tanggal_pembayaran)->format('d-m-Y') }}</td>
                            <td style="border: 1px solid black; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Rp {{ number_format($pembayaran->total_harga, 0, ',', '.') }}</td>
                            <td style="border: 1px solid black; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Rp {{ number_format($pembayaran->dibayarkan, 0, ',', '.') }}</td>
                            <td style="border: 1px solid black; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Rp {{ number_format($pembayaran->sisa_hutang, 0, ',', '.') }}</td>
                            <td style="border: 1px solid black; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Rp {{ number_format($pembayaran->bayar, 0, ',', '.') }}</td>
                            <td style="border: 1px solid black; overflow: hidden; text-overflow: ellipsis;">
                                <form action="{{ route('hutang_pelanggan.bayar.hapus', $pembayaran->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pembayaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="border: 1px solid black;">Belum ada pembayaran</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div style="margin-top: 20px;">
                {{ $pembayaranList->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
// Fungsi terbilang sederhana dalam bahasa Indonesia
function terbilang(bilangan) {
    const angka = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];

    bilangan = Math.floor(bilangan);
    if (bilangan < 12) {
        return angka[bilangan];
    } else if (bilangan < 20) {
        return terbilang(bilangan - 10) + " Belas";
    } else if (bilangan < 100) {
        return terbilang(Math.floor(bilangan / 10)) + " Puluh " + terbilang(bilangan % 10);
    } else if (bilangan < 200) {
        return "Seratus " + terbilang(bilangan - 100);
    } else if (bilangan < 1000) {
        return terbilang(Math.floor(bilangan / 100)) + " Ratus " + terbilang(bilangan % 100);
    } else if (bilangan < 2000) {
        return "Seribu " + terbilang(bilangan - 1000);
    } else if (bilangan < 1000000) {
        return terbilang(Math.floor(bilangan / 1000)) + " Ribu " + terbilang(bilangan % 1000);
    } else if (bilangan < 1000000000) {
        return terbilang(Math.floor(bilangan / 1000000)) + " Juta " + terbilang(bilangan % 1000000);
    } else {
        return "Jumlah terlalu besar";
    }
}

// Script utama
document.addEventListener('DOMContentLoaded', function () {
    const jumlahBayar = document.getElementById('jumlah_bayar');
    const sisaHutang = document.getElementById('sisa_hutang');
    const sisaHutangDenganBungaRaw = document.getElementById('sisa_hutang_dengan_bunga_raw');
    const kembalian = document.getElementById('kembalian');
    const tampilBayar = document.querySelector('.tampil-bayar');
    const tampilTerbilang = document.querySelector('.tampil-terbilang');

    const totalHarga = {{ $totalHarga }};
    const diskon = {{ $diskon }};
    const totalHargaAfterDiscount = totalHarga - (totalHarga * diskon / 100);
    const sudahDibayar = {{ $sudahDibayar }};
    const statusPembayaran = "{{ $penjualankredit->status_pembayaran }}";

    const sisaHutangDenganBunga = sisaHutangDenganBungaRaw ? parseInt(sisaHutangDenganBungaRaw.value) : 0;

    const sisaDasar = sisaHutangDenganBunga > 0 ? sisaHutangDenganBunga : (totalHargaAfterDiscount - sudahDibayar);

    if (statusPembayaran === 'lunas') {
        // Jika sudah lunas, tampil Rp 0 dan kosongkan input bayar, kembalian, sisa hutang
        tampilBayar.textContent = "Bayar: Rp 0";
        tampilTerbilang.textContent = "Nol Rupiah";
        sisaHutang.value = "Rp 0";
        kembalian.value = "Rp 0";
        if(jumlahBayar) jumlahBayar.value = 0;
        return; // hentikan eksekusi fungsi berikutnya
    }

    // Set default jumlah bayar jika kosong
    if (jumlahBayar && !jumlahBayar.value) {
        jumlahBayar.value = sisaDasar;
    }

    function updateDisplay() {
        const bayar = parseInt(jumlahBayar.value) || 0;
        const sisa = sisaDasar - bayar;

        const formKembalian = document.getElementById('form-kembalian');

        if (sisa < 0) {
            sisaHutang.value = "Rp 0";
            kembalian.value = "Rp " + Math.abs(sisa).toLocaleString('id-ID');
            if (formKembalian) formKembalian.style.display = 'block';
        } else {
            sisaHutang.value = "Rp " + sisa.toLocaleString('id-ID');
            kembalian.value = "Rp 0";
            if (formKembalian) formKembalian.style.display = 'none';
        }

        tampilBayar.textContent = "Bayar: Rp " + bayar.toLocaleString('id-ID');

        tampilTerbilang.textContent = bayar > 0 ? terbilang(bayar) + " Rupiah" : "Nol rupiah";
    }

    jumlahBayar.addEventListener('input', updateDisplay);

    // Inisialisasi tampilan
    updateDisplay();
});
</script>
@endpush





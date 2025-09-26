@extends('layouts.master')

@section('title')
    Form Pembayaran Hutang
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

.tampil-bayar,
.tampil-terbilang {
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
}
</style>
<div class="row">
    <div class="col-lg-12">
        <div class="box-header with-border">
            <table style="font-size: 18px;">
            <tr>
                <td>Supplier</td>
                <td>: {{ $pembelian->supplier->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Telepon</td>
                <td>: {{ $pembelian->supplier->telepon ?? '-' }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $pembelian->supplier->alamat ?? '-' }}</td>
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
                $totalHarga = $pembelian->total_harga;
                $sudahDibayar = $pembelian->bayar;
                $diskon = $pembelian->diskon ?? 0; // Pastikan ada kolom diskon
                $totalHargaAfterDiscount = $totalHarga - ($totalHarga * $diskon / 100); // Menghitung total harga setelah diskon dalam persen
                $sisaHutang = $totalHargaAfterDiscount - $sudahDibayar;
                $listPembayaran = $pembelian->pembayaran_hutang ?? []; // relasi hasMany di model Pembelian
            @endphp

            @if ($pembelian->status_pembayaran == 'lunas')
                <div class="alert alert-success mt-2" id="alert-lunas">
                    <span style="float: right; cursor: pointer;" onclick="document.getElementById('alert-lunas').style.display='none';">&times;</span>
                    Pembayaran sudah lunas, tidak dapat melakukan pembayaran lagi.
                </div>
            @elseif ($pembelian->status_pembayaran == 'hutang' && $sisaHutang > 0)
                <div class="alert alert-warning mt-2" id="alert-hutang">
                    <span style="float: right; cursor: pointer;" onclick="document.getElementById('alert-hutang').style.display='none';">&times;</span>
                    Masih terdapat sisa hutang, silakan lakukan pembayaran.
                </div>
            @endif

            <form action="{{ route('hutang.bayar.simpan', $pembelian->id_pembelian) }}" method="POST">
                @csrf
                <div class="row">
                    {{-- Kolom Kiri --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Total Harga</label>
                            <input type="text" class="form-control" value="Rp {{ number_format($totalHarga) }}" disabled>
                        </div>

                        <div class="form-group">
                            <label>Diskon</label>
                            <input type="text" class="form-control" value="{{ number_format($diskon, 0, ',', '.') }}%" disabled>
                        </div>

                        @if($diskon > 0)
                        <div class="form-group">
                            <label>Total Setelah Diskon</label>
                            <input type="text" class="form-control" value="Rp {{ number_format($totalHargaAfterDiscount) }}" disabled>
                        </div>
                        @endif

                        <div class="form-group">
                            <label>Total Sudah Dibayar</label>
                            <input type="text" class="form-control" value="Rp {{ number_format($sudahDibayar) }}" disabled>
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

                    {{-- Kolom Kanan --}}
                    <div class="col-md-6">

                        <div class="form-group">
                            <label>Sisa Hutang</label>
                            <input type="text" class="form-control" id="sisa_hutang" value="Rp {{ number_format($sisaHutang) }}" readonly>
                        </div>

                        @if ($sisaHutang > 0)
                        <div class="form-group">
                            <label for="metode_pembayaran">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <option value="tunai">Tunai</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_pembayaran">Tanggal Pembayaran</label>
                            <input type="date" name="tanggal_pembayaran" class="form-control" required>
                        </div>
                        @endif

                        @if ($sisaHutang > 0)
                            <div class="form-group">
                                <label for="jumlah_bayar">Jumlah Bayar</label>
                                <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control" placeholder="Masukkan nominal bayar" required>
                                <small id="terbilang" class="text-muted mt-1 d-block"></small>
                            </div>

                            {{-- Tambahkan kolom kembalian --}}
                            <div class="form-group" id="group-kembalian" style="display: none;">
                                <label for="kembalian">Kembalian</label>
                                <input type="text" id="kembalian" class="form-control" readonly>
                            </div>
                        @endif
       

                        <div class="form-group mt-4">
                            @if ($sisaHutang > 0)
                                <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                            @endif
                            <a href="{{ route('hutang.index') }}" class="btn btn-kembali ml-2">Kembali</a>
                        </div>
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
                            <td style="border: 1px solid black; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Rp {{ number_format($pembayaran->sudah_dibayar, 0, ',', '.') }}</td>
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

            <div class="d-flex justify-content-center mt-3">
                <div class="pagination pagination-lg">
                    {{ $pembayaranList->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Fungsi konversi angka ke teks bahasa Indonesia
function terbilang(nilai) {
    const angka = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];

    function konversi(n) {
        n = Math.floor(n);
        if (n < 12) return angka[n];
        else if (n < 20) return konversi(n - 10) + " Belas";
        else if (n < 100) return konversi(Math.floor(n / 10)) + " Puluh " + konversi(n % 10);
        else if (n < 200) return "Seratus " + konversi(n - 100);
        else if (n < 1000) return konversi(Math.floor(n / 100)) + " Ratus " + konversi(n % 100);
        else if (n < 2000) return "Seribu " + konversi(n - 1000);
        else if (n < 1000000) return konversi(Math.floor(n / 1000)) + " Ribu " + konversi(n % 1000);
        else if (n < 1000000000) return konversi(Math.floor(n / 1000000)) + " Juta " + konversi(n % 1000000);
        else if (n < 1000000000000) return konversi(Math.floor(n / 1000000000)) + " Miliar " + konversi(n % 1000000000);
        else return "Angka terlalu besar";
    }

    return nilai > 0 ? konversi(nilai).trim() + " Rupiah" : "Nol Rupiah";
}

document.addEventListener('DOMContentLoaded', function () {
    const jumlahBayar = document.getElementById('jumlah_bayar');
    const sisaHutang = document.getElementById('sisa_hutang');
    const tampilBayarEls = document.querySelectorAll('.tampil-bayar');
    const tampilTerbilangEls = document.querySelectorAll('.tampil-terbilang');
    const kembalianGroup = document.getElementById('group-kembalian');
    const kembalianInput = document.getElementById('kembalian');

    const totalHarga = {{ $totalHarga }};
    const diskon = {{ $diskon }};
    const totalHargaAfterDiscount = totalHarga - (totalHarga * diskon / 100);
    let sudahDibayar = {{ $sudahDibayar }};
    const statusPembayaran = "{{ $pembelian->status_pembayaran }}";

    if (statusPembayaran === 'lunas') {
        tampilBayarEls.forEach(el => {
            el.textContent = "Bayar: Rp 0";
        });

        tampilTerbilangEls.forEach(el => {
            el.textContent = "Nol Rupiah";
        });

        if (jumlahBayar) {
            jumlahBayar.value = 0;
            jumlahBayar.readOnly = true;
        }

        if (sisaHutang) {
            sisaHutang.value = "Rp 0";
        }

        return;
    }

    function updateTampilanBayarDanTerbilang(jumlah) {
        const bayarBaru = parseInt(jumlah) || 0;
        const totalBayarSementara = sudahDibayar + bayarBaru;
        const sisa = totalHargaAfterDiscount - totalBayarSementara;

        // Update sisa hutang
        sisaHutang.value = "Rp " + (sisa > 0 ? sisa : 0).toLocaleString('id-ID');

        // Update tampilan bayar
        tampilBayarEls.forEach(el => {
            el.textContent = "Bayar: Rp " + bayarBaru.toLocaleString('id-ID');
        });

        // Update terbilang
        tampilTerbilangEls.forEach(el => {
            el.textContent = terbilang(bayarBaru);
        });

        // Kembalian
        if (sisa < 0) {
            const kembalian = Math.abs(sisa);
            kembalianInput.value = "Rp " + kembalian.toLocaleString('id-ID');
            kembalianGroup.style.display = 'block';
        } else {
            kembalianInput.value = "";
            kembalianGroup.style.display = 'none';
        }
    }

    // Panggil saat awal
    updateTampilanBayarDanTerbilang(jumlahBayar.value);

    jumlahBayar.addEventListener('input', function () {
        updateTampilanBayarDanTerbilang(jumlahBayar.value);
    });
});

</script>
@endpush
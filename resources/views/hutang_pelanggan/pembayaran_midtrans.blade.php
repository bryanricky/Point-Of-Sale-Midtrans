@extends('layouts.master')

@section('title', 'Informasi Pembayaran Terakhir')
<style>
    .bg-green-transparent {
        background-color: rgba(144, 238, 144, 0.5);
    }
</style>

@section('content')
<div class="col-lg-12">

    {{-- Informasi Supplier dan Transaksi --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <table class="table table-bordered" style="font-size: 18px;">
                <tbody>
                    <tr>
                        <th style="width: 30%;">Nama Pelanggan</th>
                        <td>: {{ $penjualankredit->pelanggan->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Telepon</th>
                        <td>: {{ $penjualankredit->pelanggan->telepon ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>: {{ $penjualankredit->pelanggan->alamat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Total Harga</th>
                        <td>: Rp {{ number_format($penjualankredit->total_harga, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Sudah Dibayar</th>
                        <td>: Rp {{ number_format($penjualankredit->bayar, 0, ',', '.') }}</td>
                    </tr>
                    @php
                        $totalHarga = $penjualankredit->total_harga;
                        $sudahDibayar = $penjualankredit->bayar;
                        $diskon = $penjualankredit->diskon ?? 0;
                        $totalHargaAfterDiscount = $totalHarga - ($totalHarga * $diskon / 100);
                        // Jika sudah dibayar lebih besar dari total setelah diskon, hutang dianggap lunas
                        $sisaHutang = max(0, $totalHargaAfterDiscount - $sudahDibayar);
                    @endphp
                    <tr>
                        <th class="text-danger">Sisa Hutang</th>
                        <td class="text-danger fw-bold">: Rp {{ number_format($sisaHutang, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Form Pembayaran --}}
    <div class="card shadow-sm">
    <div class="card-header text-white" style="background: transparent;">
        <h5 class="mb-0 px-3 py-2 text-white" style="font-size: 25px; background-color: rgba(144, 238, 144, 0.5); display: inline-block; border-radius: 0px;">
            Informasi Pembayaran Baru
        </h5>
    </div>
    </div>
        <div class="card-body">
            <form id="midtrans-form" style="font-size: 18px;">
                @csrf

                <p class="mb-2">
                    <strong>Tanggal Pembayaran:</strong><br>
                    <span>{{ old('tanggal_pembayaran', $pembelian->tanggal_pembayaran ?? date('Y-m-d')) }}</span>
                </p>

                <p class="mb-4">
                    <strong>Jumlah Bayar:</strong><br>
                    <span class="text-success fw-bold fs-4">
                        Rp {{ number_format(old('jumlah_bayar', session('jumlah_bayar') ?? $jumlah_bayar ?? 0), 0, ',', '.') }}
                    </span>
                </p>

                <input type="hidden" id="snap_token" value="{{ session('snap_token') }}">

                <div class="d-flex gap-2">
                    <button type="button" id="pay-button" class="btn btn-success flex-grow-1" data-id="{{ $penjualankredit->id }}">
                        <i class="fa fa-credit-card"></i> Bayar
                    </button>
                    <a href="{{ route('hutang_pelanggan.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- TODO: Remove ".sandbox" from script src URL for production environment. Also input your client key in "data-client-key" -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<SB-Mid-client-It33ujTu9rnxYkH7>"></script>
<script type="text/javascript">
    document.getElementById('pay-button').addEventListener('click', function () {
        const snapToken = document.getElementById('snap_token')?.value;

        if (!snapToken) {
            alert('Token pembayaran tidak ditemukan.');
            return;
        }

        snap.pay(snapToken, {
            onSuccess: function (result) {
                console.log('Pembayaran sukses:', result);
                // Redirect ke halaman sukses
                window.location.href = "{{ route('hutang_pelanggan.success', ['id' => $penjualankredit->id_penjualan]) }}";
            },
            onPending: function (result) {
                console.log('Pembayaran pending:', result);
                alert('Pembayaran sedang diproses.');
            },
            onError: function (result) {
                console.error('Pembayaran gagal:', result);
                alert('Terjadi kesalahan saat pembayaran.');
            },
            onClose: function () {
                alert('Anda menutup popup tanpa menyelesaikan pembayaran.');
            }
        });
    });
</script>
@endpush
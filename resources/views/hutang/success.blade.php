@extends('layouts.master')

@section('content')
<div class="container mt-4">
    <h2>Pembayaran Berhasil!</h2>
    <p>Terima kasih. Pembayaran untuk transaksi <strong>ID Penjualan: {{ $id }}</strong> telah berhasil.</p>
    <a href="{{ route('hutang.bayar.form', $id) }}" class="btn btn-primary">Kembali</a>
    {{-- Tombol Cetak Struk --}}
    <a href="{{ route('hutang.cetak_struk_tunai', $id) }}" class="btn btn-success" target="_blank" style="text-decoration: none;">
        Cetak Struk
    </a>
</div>
@endsection

@extends('layouts.master')

@section('content')
<div class="container mt-4">

    {{-- Alert Hijau Hanya untuk Pesan --}}
    <div class="alert alert-success">
        <h4 class="alert-heading">Pembayaran Tunai Berhasil!</h4>
        <p>Terima kasih, pembayaran Anda telah berhasil dicatat.</p>
    </div>

    {{-- Tombol Aksi di Luar Alert --}}
    <div class="mt-3 d-flex gap-2">
        <a href="{{ route('hutang.bayar.form', $id) }}" class="btn btn-primary" style="text-decoration: none;">Kembali</a>
        <a href="{{ route('hutang.cetak_struk_tunai', $id) }}" class="btn btn-warning" target="_blank" style="text-decoration: none;">
            Cetak Struk
        </a>
    </div>
</div>
@endsection

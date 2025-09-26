<!-- resources/views/transaksi/pembayaran-midtrans.blade.php -->

@extends('layouts.master')

@section('content')
    <div class="container">
        <h3>Proses Pembayaran</h3>
        
        <div class="alert alert-info">
            <strong>Silahkan lakukan pembayaran melalui Midtrans.</strong>
        </div>

        <div id="midtrans-container">
            <button id="pay-button" class="btn btn-primary">
                Bayar Sekarang
            </button>
        </div>
    </div>

    @push('scripts')
        <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        
        <script>
            var snapToken = "{{ $snap_token }}"; // Dapatkan snap token dari controller

            document.getElementById('pay-button').onclick = function () {
                snap.pay(snapToken, {
                    onSuccess: function (result) {
                        console.log(result);
                        window.location.href = "{{ route('pembelian.selesai') }}"; // Redirect setelah sukses
                    },
                    onPending: function (result) {
                        alert("Pembayaran Anda pending");
                        console.log(result);
                    },
                    onError: function (result) {
                        alert("Pembayaran gagal");
                        console.log(result);
                    },
                    onClose: function () {
                        alert('Anda menutup popup tanpa melakukan pembayaran');
                    }
                });
            }
        </script>
    @endpush
@endsection

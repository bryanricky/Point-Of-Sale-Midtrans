@extends('layouts.master')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bayar dengan Midtrans</title>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<SB-Mid-client-It33ujTu9rnxYkH7>"></script>
</head>
<body>
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

    <script>
        document.getElementById('pay-button').addEventListener('click', function () {
            snap.pay("{{ $snap_token }}", {
                onSuccess: function(result){
                    alert("Pembayaran berhasil!");
                    console.log(result);
                    window.location.href = "{{ route('midtranspelanggan.success')}}";
                },
                onPending: function(result){
                    alert("Menunggu pembayaran.");
                    console.log(result);
                },
                onError: function(result){
                    alert("Pembayaran gagal!");
                    console.log(result);
                }
            });
        });
    </script>
</body>
</html>

@endsection
@push('scripts')
@endpush
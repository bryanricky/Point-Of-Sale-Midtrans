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
    <h1>Proses Pembayaran</h1>

    <button id="pay-button">Bayar Sekarang</button>

    <script>
        document.getElementById('pay-button').addEventListener('click', function () {
            snap.pay("{{ $snapToken }}", {
                onSuccess: function(result){
                    alert("Pembayaran berhasil!");
                    console.log(result);
                    window.location.href = "{{ route('success') }}";
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cetak Barcode</title>

    <style>
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <table width="100%">
        <tr>
        @foreach ($dataproduk as $produk)
            <td class="text-center" style="border: 1px solid #333; padding: 5px;">
                <p style="margin-bottom: 2px;">{{ $produk->nama_produk }} - Rp. {{ format_uang($produk->harga_jual) }}</p>
                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($produk->kode_produk, 'C39') }}" 
                    alt="{{ $produk->kode_produk }}"
                    width="180"
                    height="60"
                    style="display: block; margin: 0 auto;">
                <p style="margin-top: 2px;">{{ $produk->kode_produk }}</p>
            </td>
            @if ($no++ % 3 == 0)
                </tr><tr>
            @endif
        @endforeach
        </tr>
    </table>
</body>
</html>
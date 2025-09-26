<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pembayaran</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            max-width: 280px;
            margin: auto;
            color: #000;
            background: #fff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .header, .footer, .info, .total {
            text-align: center;
            margin-bottom: 10px;
        }

        .header img {
            max-height: 50px;
            margin-bottom: 5px;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .item {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            padding: 2px 0;
        }

        .bold {
            font-weight: bold;
        }

        .thank {
            margin-top: 10px;
        }

        .status-hutang {
            color: red;
            font-weight: bold;
        }

        .status-nonhutang {
            color: blue;
            font-weight: bold;
        }

        .spacer {
            height: 10px;
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <div class="header">
            {{-- Logo toko jika ada --}}
            {{-- <img src="{{ asset('path/to/logo.png') }}" alt="Logo"> --}}
            <div class="bold">{{ strtoupper($setting->nama_perusahaan ?? 'TOKO') }}</div>
            <div>{{ $setting->alamat ?? '-' }}</div>
            <div>{{ $setting->telepon ?? '-' }}</div>
            <div class="line"></div>
        </div>

        <div class="info">
            <div class="item">
                <span>Nama</span>
                <span>{{ $pembelian->supplier->nama ?? '-' }}</span>
            </div>
            <div class="item">
                <span>ID Supplier</span>
                <span>{{ $pembelian->id_supplier ?? '-' }}</span>
            </div>
            <div class="item">
                <span>Tanggal Transaksi</span>
                <span>{{ \Carbon\Carbon::parse($pembelian->created_at)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="item">
                <span>Status</span>
                <span class="{{ ($pembelian->status_pembayaran == 'hutang') ? 'status-hutang' : 'status-nonhutang' }}">
                    {{ ucfirst($pembelian->status_pembayaran ?? '-') }}
                </span>
            </div>
            <div class="line"></div>
        </div>

        <div class="total">
            <table width="100%" style="border-collapse: collapse;">
                @foreach ($detail as $item)
                    <tr>
                        <td colspan="2" style="text-align: left; word-break: break-word;">
                            {{ $item->produk->nama_produk }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="font-size: 12px; text-align: left;">
                            {{ $item->jumlah }} x {{ number_format($item->harga_beli, 0, ',', '.') }}
                        </td>
                        <td style="width: 20%; text-align: right;">
                            {{ number_format($item->jumlah * $item->harga_beli, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </table>

            <div class="item" style="margin-top: 5px;">
                <span>Subtotal</span>
                <span>{{ number_format($pembelian->total_harga ?? 0, 0, ',', '.') }}</span>
            </div>

            @if(!empty($pembelian->diskon) && $pembelian->diskon > 0)
                <div class="item">
                    <span>Diskon</span>
                    <span>{{ $pembelian->diskon }}%</span>
                </div>
            @endif

            @if(!empty($pembelian->harga_diskon) && $pembelian->harga_diskon > 0)
                <div class="item">
                    <span>Harga Diskon</span>
                    <span>{{ number_format($pembelian->harga_diskon, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="spacer"></div>

            <div class="item">
                <span>Dibayar</span>
                <span>{{ number_format($pembayaranSekarang->jumlah_bayar ?? $totalBayar ?? 0, 0, ',', '.') }}</span>
            </div>

            @if(!empty($pembayaranSekarang->kembalian) && $pembayaranSekarang->kembalian > 0)
                <div class="item">
                    <span>Kembalian</span>
                    <span>{{ number_format($pembayaranSekarang->kembalian, 0, ',', '.') }}</span>
                </div>
            @endif

            @if ($pembelian->status_pembayaran === 'hutang')
                <div class="item">
                    <span>Sisa Hutang</span>
                    <span>{{ number_format($sisaHutang ?? 0, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="line"></div>

             @if ($pembelian->status_pembayaran === 'hutang')
                <div class="item">
                    <span>Total Sudah Dibayar</span>
                    <span>{{ number_format($totalBayar ?? 0, 0, ',', '.') }}</span>
                </div>
             @endif

             <div class="line"></div>

        </div>

        <div class="footer thank">
            <p>-- Terima kasih --</p>
            <p>Simpan struk ini sebagai bukti pembayaran</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>

</body>
</html>

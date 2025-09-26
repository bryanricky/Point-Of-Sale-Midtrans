<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        h2, h4 {
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .header, .footer {
            width: 100%;
            text-align: center;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table th, .table td {
            border: 1px solid #444;
            padding: 6px 8px;
            text-align: center;
        }

        .table th {
            background-color: #f0f0f0;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            margin-top: 20px;
            float: right;
            width: 40%;
        }

    </style>
</head>
<body>

    <h2>Laporan Penjualan</h2>
    <h4>Periode: {{ tanggal_indonesia($awal, false) }} s/d {{ tanggal_indonesia($akhir, false) }}</h4>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Total Item</th>
                <th>Total Harga</th>
                <th>Diskon</th>
                <th>Total Bayar</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach ($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ tanggal_indonesia($item->created_at, true) }}</td>
                    <td>{{ $item->pelanggan->nama ?? 'Umum' }}</td>
                    <td>{{ $item->total_item }}</td>
                    <td class="text-right">{{ format_uang($item->bayar) }}</td>
                    <td class="text-right">{{ $item->diskon }}%</td>
                    <td class="text-right">{{ format_uang($item->bayar) }}</td>
                </tr>
                @php $total += $item->bayar; @endphp
            @endforeach

            <!-- Baris Total Penjualan -->
            <tr>
                <td colspan="6" style="text-align: center;"><strong>Total Penjualan</strong></td>
                <td class="text-right"><strong>{{ format_uang($total) }}</strong></td>
            </tr>
        </tbody>
    </table>

</body>
</html>

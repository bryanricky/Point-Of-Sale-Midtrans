<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran</title>
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
            text-align: center;
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

    <h2>Laporan Pengeluaran</h2>
    <h4>Periode: {{ tanggal_indonesia($awal, false) }} s/d {{ tanggal_indonesia($akhir, false) }}</h4>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Tanggal</th>
                <th>Deskripsi</th>
                <th class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse ($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ tanggal_indonesia($item->created_at, true) }}</td>
                    <td>{{ $item->deskripsi }}</td>
                    <td class="text-right">{{ format_uang($item->nominal) }}</td>
                </tr>
                @php $total += $item->nominal; @endphp
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Tidak ada data pengeluaran</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="3" style="text-align: center;"><strong>Total Pengeluaran</strong></td>
                <td class="text-right"><strong>{{ format_uang($total) }}</strong></td>
            </tr>
        </tbody>
    </table>

</body>
</html>

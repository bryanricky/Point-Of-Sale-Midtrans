<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Pendapatan</title>

    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .table-container {
            width: max-content;
            margin: 40px auto;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        .table {
            border-collapse: collapse;
            min-width: 700px;
        }

        .table th, .table td {
            border: 2px solid #333;
            padding: 10px;
            text-align: center;
        }

        .table th {
            background-color: #d3d3d3;
            font-weight: bold;
        }

        .table td {
            background-color: #fff;
        }

        .text-center {
            text-align: center;
        }

        .header-title {
            margin-top: 20px;
            font-size: 24px;
            font-weight: bold;
        }

        .sub-title {
            font-size: 18px;
        }

        h4 {
            font-size: 16px;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <div class="text-center header-title">
        <h3>Laporan Pendapatan</h3>
        <h4 class="sub-title">
            Tanggal {{ tanggal_indonesia($awal, false) }}
            s/d
            Tanggal {{ tanggal_indonesia($akhir, false) }}
        </h4>
    </div>

    <div class="table-container">
        <table class="table table-striped" style="background-color: #f0f0f0;">
            <thead style="background-color: #d3d3d3;">
                <tr>
                    <th width="5%">No</th>
                    <th>Tanggal</th>
                    <th>Penjualan</th>
                    <th>Penjualan Kredit</th>
                    <th>Pembayaran Piutang</th>
                    <th>Pembelian</th>
                    <th>Pembayaran Hutang</th>
                    <!-- <th>Pengeluaran</th> -->
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $key => $row)
                    <tr>
                        @foreach ($row as $col)
                            <td>{{ $col }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cetak Kartu Member</title>

    <style>
        .box {
            position: relative;
            width: 85.60mm;
            height: 53.98mm;
        }
        .card {
            width: 85.60mm;
        }
        .logo {
            position: absolute;
            top: -28pt;
            right: 0pt;
            font-size: 16pt;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: bold;
            color: #fff !important;
        }
        .logo p {
            text-align: right;
            margin-right: 16pt;
        }
        .logo img {
            position: absolute;
            margin-top: -15pt;
            width: 60px;
            height: 60px;
            right: 15pt;
        }
        .nama {
            position: absolute;
            top: 65pt;
            right: 16pt;
            font-size: 12pt;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: bold;
            color: #fff !important;
        }
        .telepon {
            position: absolute;
            top: 85pt;
            right: 16pt;
            color: #fff;
        }
        .barcode {
            position: absolute;
            top: 70pt;
            left: .860rem;
            border: 1px solid #fff;
            padding: .5px;
            background: #fff;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
<section style="padding-top: 10mm; padding-left: 0mm;">
        <table width="100%">
            @foreach ($datamember as $key => $data)
                <tr>
                    @foreach ($data as $item)
                    <td class="text-center" style="padding-left: 2mm; padding-right: 2mm;">
                            <div class="box">
                                {{-- Gambar latar belakang kartu --}}
                                <img src="{{ public_path($setting->path_kartu_member) }}" alt="card" class="card">
                                
                                {{-- Logo dan nama toko --}}
                                <div class="logo">
                                    <p>{{ $setting->nama_perusahaan }}</p>
                                    <img src="{{ public_path($setting->path_logo) }}" alt="logo">
                                </div>

                                {{-- Nama member --}}
                                <div class="nama">{{ $item->nama }}</div>

                                {{-- Nomor telepon --}}
                                <div class="telepon">{{ $item->telepon }}</div>

                                {{-- QR Code --}}
                                <div class="barcode text-left">
                                    <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($item->kode_member, 'QRCODE') }}" alt="qrcode" height="45" width="45">
                                </div>
                            </div>
                        </td>

                        {{-- Jika hanya 1 data, tambahkan kolom kosong untuk keseimbangan --}}
                        @if (count($data) == 1)
                            <td class="text-center" style="width: 50%;"></td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </table>
    </section>
</body>
</html>

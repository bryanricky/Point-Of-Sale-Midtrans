@extends('layouts.master')

@section('title')
    Daftar Piutang Pelanggan
@endsection

<style>
    .table thead th {
        background-color: #cccccc !important;
        color: #333;
        text-align: center; /* Buat header center */
    }

    table.table th, table.table td {
        border: 2px solid #dee2e6 !important;
    }

    .table-font-lg {
        font-size: 16px;
    }

     /* Tambahan untuk lebar kolom */
     th.kolom-uang, td.kolom-uang {
        width: 130px;
        white-space: nowrap;
    }
</style>

@section('content')
<div class="row">
    <div class="table-responsive col-lg-12">
    <div class="box-header with-border">
                <a href="{{ route('hutang_pelanggan.pembayaran_lunas') }}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-list"></i> Daftar Lunas</a>
            </div>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelanggan</th>
                    <th>Total Harga</th>
                    <th>Diskon</th>
                    <th class="kolom-uang">Harga Diskon</th>
                    <th class="kolom-uang">Total Sudah Dibayar</th>
                    <th class="kolom-uang">Sisa Hutang</th>
                    @if ($adaBunga)
                        <th class="kolom-uang kolom-bunga">Sisa + Bunga 2%</th>
                    @endif
                    <th>Jatuh Tempo</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($hutang as $index => $item)
                @php
                    $jatuhTempo = \Carbon\Carbon::parse($item->jatuh_tempo);
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="text-align: center;">
                        <div>{{ $item->pelanggan->nama }}</div>

                        @if($item->lama_terlambat)
                            <div>
                                <span class="text-danger" style="font-size: 15px;">
                                    <i class="fa fa-exclamation-triangle"></i>Terlambat: {{ $item->lama_terlambat }}
                                </span>
                            </div>
                        @endif

                        @if ($item->peringatan_tagihan)
                            <div>
                                <span style="font-size: 15px; color: blue;">
                                    {{ $item->peringatan_tagihan }}
                                </span>
                            </div>
                        @endif
                    </td>

                    <td style="text-align: center;">{{ 'Rp. ' . number_format($item->total_harga, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $item->diskon }}%</td>
                    <td class="kolom-uang" style="text-align: center;">
                        @if($item->diskon > 0)
                            {{ 'Rp. ' . number_format($item->total_harga - ($item->total_harga * $item->diskon / 100), 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="kolom-uang" style="text-align: center;">{{ 'Rp. ' . number_format($item->bayar, 0, ',', '.') }}</td>
                    <td class="kolom-uang" style="text-align: center;">{{ 'Rp. ' . number_format($item->sisa_hutang, 0, ',', '.') }}</td>
                    @if ($adaBunga)
                        <td class="kolom-uang kolom-bunga" style="text-align: center;">
                            @if ($item->bunga > 0)
                                <div style="font-size: 15px; font-weight: bold;">
                                    Rp {{ number_format($item->sisa_dengan_bunga, 0, ',', '.') }}
                                </div>
                                <div style="font-size: 15px;" class="text-danger">
                                    (Bunga: Rp {{ number_format($item->bunga, 0, ',', '.') }})
                                </div>
                            @else
                                -
                            @endif
                        </td>
                    @endif

                    <td class="text-center">{{ $jatuhTempo->format('d-m-Y') }}</td>
                    <td class="text-center">
                        <a href="{{ route('hutang_pelanggan.bayar', $item->id_penjualan, ) }}" class="btn btn-success">Bayar</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.table').DataTable({
           "processing": true,
            "serverSide": false,
            "order": [],
        });
    });
</script>
@endpush
@endsection

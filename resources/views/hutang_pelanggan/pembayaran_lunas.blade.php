@extends('layouts.master')

@section('title')
    Daftar Piutang Lunas Pelanggan
@endsection

<style>
    .table thead th {
        background-color: #cccccc !important; /* header abu-abu */
        color: #333;
    }

    table.table th, table.table td {
        border: 2px solid #dee2e6 !important;
    }

    .bg-success {
        background-color: #28a745 !important;
    }

    .text-lunas {
        color: #ffffff; /* You can change this color as needed */
        font-size: 18px; /* Adjust the font size as needed */
        font-weight: normal; /* Optional: Make the text bold */
        padding-left: 14px;  /* Increase the padding */
        padding-right: 14px; /* Increase the padding */
    }
</style>

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box-header with-border">
                <a href="{{ route('hutang_pelanggan.index') }}" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-list"></i> Daftar Piutang</a>
            </div>
        <table id="hutang-table" class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Pelanggan</th>
                    <th class="text-center">Total Harga</th>
                    <th class="text-center">Diskon</th>
                    <th class="text-center">Harga Diskon</th>
                    <th class="text-center">Total Sudah Dibayar</th>
                    <th class="text-center">Sisa Hutang</th>
                    <th class="text-center">Status Pembayaran</th>
                    <th class="text-center">Jatuh Tempo</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dataLunas as $index => $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $item->pelanggan->nama?? '-' }}</td>
                    <td class="text-center">Rp. {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->diskon ?? 0 }}%</td>
                    <td class="text-center" style="text-align: center;">
                        @if($item->diskon > 0)
                            {{ 'Rp. ' . number_format($item->total_harga - ($item->total_harga * $item->diskon / 100), 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">Rp. {{ number_format($item->bayar, 0, ',', '.') }}</td>
                    <td class="text-center">Rp. {{ number_format($item->sisa_hutang, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($item->status_pembayaran == 'lunas')
                            <span class="bg-success px-2 py-1 rounded text-white text-lunas">{{ ucfirst($item->status_pembayaran) }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->jatuh_tempo)->translatedFormat('d F Y') }}</td>
                    <td class="text-center"><a href="{{ route('hutang_pelanggan.bayar.form', $item->id_penjualan) }}" class="btn btn-sm btn-info">Detail</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#hutang-table').DataTable({
            "processing": true,
            "serverSide": false,
            "order": [],
        });
    });
</script>
@endpush
@endsection

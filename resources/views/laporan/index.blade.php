@extends('layouts.master')

@php
    $isKasir = auth()->user()->level == 2;
@endphp

@section('title')
    @if ($isKasir)
        Laporan Pendapatan Harian
    @else
        Laporan Pendapatan {{ tanggal_indonesia($tanggalAwal, false) }} s/d {{ tanggal_indonesia($tanggalAkhir, false) }}
    @endif
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css">
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Laporan</li>
@endsection

<style>
    .table thead th {
        background-color: #cccccc !important;
        color: #333;
    }

    table.table th, table.table td {
        border: 2px solid #dee2e6 !important;
    }

    .hover-scale:hover {
        transform: scale(1.1);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    th.text-center {
        text-align: center !important;
        vertical-align: middle !important;
    }

    .box-body.table-responsive {
        position: relative;
    }
</style>

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                @if(auth()->user()->level != 2)
                    <button onclick="updatePeriode()" class="btn btn-info btn-sm btn-flat" style="margin-right: 3px;">
                        <i class="fa fa-plus-circle"></i> Ubah Periode
                    </button>
                @endif

                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm btn-flat dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-file-pdf-o"></i> Export PDF <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('laporan.export_pdf', ['jenis' => 'pendapatan', 'awal' => $tanggalAwal, 'akhir' => $tanggalAkhir]) }}" target="_blank">Laporan Pendapatan</a></li>
                        <li><a href="{{ route('laporan.export_pdf', ['jenis' => 'penjualan', 'awal' => $tanggalAwal, 'akhir' => $tanggalAkhir]) }}" target="_blank">Laporan Penjualan</a></li>
                        <li><a href="{{ route('laporan.export_pdf', ['jenis' => 'pembayaran', 'awal' => $tanggalAwal, 'akhir' => $tanggalAkhir]) }}" target="_blank">Laporan Pembelian</a></li>
                        <li><a href="{{ route('laporan.export_pdf', ['jenis' => 'pembayaranpelanggan', 'awal' => $tanggalAwal, 'akhir' => $tanggalAkhir]) }}" target="_blank">Laporan Penjualan Kredit</a></li>
                        <li><a href="{{ route('laporan.export_pdf', ['jenis' => 'pembayaran_hutang', 'awal' => $tanggalAwal, 'akhir' => $tanggalAkhir]) }}" target="_blank">Laporan Hutang</a></li>
                        <li><a href="{{ route('laporan.export_pdf', ['jenis' => 'pembayaran_hutangpelanggan', 'awal' => $tanggalAwal, 'akhir' => $tanggalAkhir]) }}" target="_blank">Laporan Piutang</a></li>
                    </ul>
                </div>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered" id="laporan-table" style="background-color: #f0f0f0; table-layout: fixed;">
                    <thead style="background-color: #d3d3d3;">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="13%" class="text-center">Tanggal</th>
                            <th width="14%" class="text-center">Penjualan</th>
                            <th width="14%" class="text-center">Penjualan Kredit</th>
                            <th width="14%" class="text-center">Pembayaran Piutang</th>
                            <th width="14%" class="text-center">Pembelian</th>
                            <th width="14%" class="text-center">Pembayaran Hutang</th>
                            <th width="13%" class="text-center">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('laporan.form')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<script>
    let table;

    $(function () {
        table = $('#laporan-table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('laporan.data', [$tanggalAwal, $tanggalAkhir]) }}',
            },
            columns: [
                {data: 'DT_RowIndex', className: 'text-center', searchable: false, sortable: false},
                {data: 'tanggal', className: 'text-center'},
                {data: 'penjualan', className: 'text-center'},
                {data: 'pembayaranpelanggan', className: 'text-center'},
                {data: 'pembayaran_hutangpelanggan', className: 'text-center'},
                {data: 'pembayaran', className: 'text-center'},
                {data: 'pembayaran_hutang', className: 'text-center'},
                {data: 'pendapatan', className: 'text-center'}
            ],
            dom: 'Brt',
            bSort: false,
            bPaginate: false,
            fixedHeader: true
        });

        // Pastikan fixed header aktif
        new $.fn.dataTable.FixedHeader(table);

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    function updatePeriode() {
        $('#modal-form').modal('show');
    }
</script>
@endpush

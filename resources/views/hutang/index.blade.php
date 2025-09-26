@extends('layouts.master')

@section('title')
    Daftar Hutang Ke Supplier
@endsection

<style>
    .table thead th {
        background-color: #cccccc !important;
        color: #333;
        text-align: center;
        vertical-align: middle;
    }

    table.table th, table.table td {
        border: 2px solid #dee2e6 !important;
        vertical-align: middle;
    }

    .table-font-lg {
        font-size: 16px;
    }

    /* Atur lebar kolom */
    th.col-no, td.col-no {
        width: 40px;
    }

    th.col-supplier, td.col-supplier {
        width: 160px;
        white-space: nowrap;
    }

    th.col-total, td.col-total,
    th.col-diskon, td.col-diskon,
    th.col-dibayar, td.col-dibayar,
    th.col-sisa, td.col-sisa,
    th.col-harga-diskon, td.col-harga-diskon {
        width: 130px;
        white-space: nowrap;
    }

    th.col-jatuh, td.col-jatuh {
        width: 150px;
        white-space: nowrap;
    }

    th.col-action, td.col-action {
        width: 90px;
    }
</style>
@php
    use Illuminate\Support\Str;
@endphp


@section('content')
<div class="row">
    <div class="col-lg-12 table-responsive">
        {{-- Tombol Tambah Pembayaran --}}
        <div class="box-header with-border">
                <a href="{{ route('hutang.pembayaran') }}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-list"></i> Daftar Lunas</a>
            </div>
        <table id="hutang-table" class="table table-bordered text-center table-font-lg">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-supplier">Supplier</th>
                    <th class="col-total">Total Harga</th>
                    <th class="col-diskon">Diskon</th>
                    <th class="col-harga-diskon">Harga Diskon</th>
                    <th class="col-dibayar">Sudah Dibayar</th>
                    <th class="col-sisa">Sisa Hutang</th>
                    <th class="col-jatuh">Jatuh Tempo</th>
                    <th class="col-action">Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($dataHutang as $index => $hutang)
                <tr>
                    <td class="col-no">{{ $loop->iteration }}</td>
                    <td class="col-supplier">{{ $hutang->supplier->nama }}
                        @if ($hutang->peringatan)
    <br>
    <span class="{{ Str::contains($hutang->peringatan, 'Telat bayar') ? 'text-danger' : 'text-primary' }}" style="margin-top: 5px;">
        <i class="fa fa-exclamation-triangle"></i> {!! $hutang->peringatan !!}
    </span>
@endif

                    </td>
                    <td class="col-total">{{ 'Rp. ' . number_format($hutang->total_harga, 0, ',', '.') }}</td>
                    <td class="col-diskon">{{ $hutang->diskon }}%</td>
                    <td class="col-harga-diskon">
                        @if($hutang->diskon > 0)
                            {{ 'Rp. ' . number_format($hutang->total_harga - ($hutang->total_harga * $hutang->diskon / 100), 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="col-dibayar">{{ 'Rp. ' . number_format($hutang->bayar, 0, ',', '.') }}</td>
                    <td class="col-sisa">{{ 'Rp. ' . number_format($hutang->sisa_hutang, 0, ',', '.') }}</td>
                    <td class="col-jatuh">
                        {{ \Carbon\Carbon::parse($hutang->jatuh_tempo)->format('d-m-Y') }}
                        
                    </td>
                    <td class="col-action">
                        <a href="{{ route('hutang.bayar.form', $hutang->id_pembelian) }}" class="btn btn-success btn-sm">Bayar</a>
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
        $('#hutang-table').DataTable({
            "processing": true,
            "serverSide": false,
            "order": [],
        });
    });
</script>
@endpush
@endsection

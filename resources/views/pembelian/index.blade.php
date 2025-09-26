@extends('layouts.master')

@section('title')
    Daftar Pembelian Produk Ke Supplier
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Pembelian</li>
@endsection
<style>
    .bg-success {
        background-color: #28a745 !important;
    }

    .bg-warning {
        background-color: #2196f3 !important;
    }

    .bg-danger {
        background-color: #dc3545 !important;
    }

    .text-white {
        color: white !important;
    }

    .text-dark {
        color: #343a40 !important;
    }

    th {
        background-color: #cccccc !important;
    }

    table.table th, table.table td {
        border: 2px solid #dee2e6 !important;
    }

    .hover-scale:hover {
        transform: scale(1.1);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .text-lunas {
        color: #ffffff; /* You can change this color as needed */
        font-size: 18px; /* Adjust the font size as needed */
        font-weight: normal; /* Optional: Make the text bold */
        padding-left: 25px;  /* Increase the padding */
        padding-right: 25px; /* Increase the padding */
    }
    .text-tunai {
        color: #ffffff; /* You can change this color as needed */
        font-size: 18px; /* Adjust the font size as needed */
        font-weight: normal; /* Optional: Make the text bold */
        padding-left: 5px;  /* Increase the padding */
        padding-right: 5px; /* Increase the padding */
    }
    .text-hutang {
        color: #ffffff; /* You can change this color as needed */
        font-size: 18px; /* Adjust the font size as needed */
        font-weight: normal; /* Optional: Make the text bold */
        padding-left: 20px;  /* Increase the padding */
        padding-right: 20px; /* Increase the padding */
    }

    .btn-container {
    margin-left: 10px;  /* Add space to the left */
    margin-right: 10px; /* Add space to the right */
    }

    .btn-group .btn {
        margin: 0 5px;  /* Add space between the buttons */
    }

</style>

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addForm()" class="btn btn-success btn-sm btn-flat"><i class="fa fa-plus-circle"></i> Transaksi Baru</button>
                <a href="{{ route('hutang.index') }}" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-list"></i> Daftar Hutang</a>
                @empty(! session('id_pembelian'))
                <a href="{{ route('pembelian_detail.index') }}" class="btn btn-info btn-sm btn-flat"><i class="fa fa-pencil"></i> Transaksi Aktif</a>
                @endempty
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered border-dark table-hover shadow-sm table-pembelian">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Supplier</th>
                            <th class="text-center">Total Item</th>
                            <th class="text-center">Total Harga</th>
                            <th class="text-center">Diskon</th>
                            <th class="text-center">Harga Diskon</th>
                            <!-- <th class="text-center">Total Bayar</th>
                            <th class="text-center">Sisa Hutang</th> -->
                            <th class="text-center">Status</th>
                            <th class="text-center">Jatuh Tempo</th>
                            <th width="10%" class="text-center"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('pembelian.supplier')
@includeIf('pembelian.detail')
@endsection

@push('scripts')
<script>
    let table, table1;

    $(function () {
        table = $('.table-pembelian').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('pembelian.data') }}',
            },
            columns: [
                { data: 'DT_RowIndex', searchable: false, sortable: false, className: 'text-center' },
                { data: 'tanggal', className: 'text-center' },
                { data: 'supplier', className: 'text-center' },
                { data: 'total_item', className: 'text-center' },
                { data: 'total_harga', className: 'text-center' },
                { data: 'diskon', className: 'text-center' },
                { data: 'harga_diskon', className: 'text-center' },
                // { data: 'bayar', className: 'text-center' },
                // { data: 'sisa_hutang', className: 'text-center' },
                { data: 'status_pembayaran', className: 'text-center' },
                { data: 'jatuh_tempo', className: 'text-center' },
                { data: 'aksi', searchable: false, sortable: false, className: 'text-center' },
            ],
            createdRow: function (row, data, dataIndex) {
            var status = data.status_pembayaran;
            var statusCell = $(row).find('td').eq(7); // Menargetkan sel status pembayaran

            // Membuat elemen span dengan kelas sesuai status
            var statusElement = $('<span>').text(status);

            // Menambahkan kelas berdasarkan status pembayaran
            if (status === 'Lunas') {
                statusElement.addClass('bg-success text-white text-lunas px-2 py-1 rounded'); // Status pembayaran Lunas (warna hijau)
            } else if (status === 'Nonhutang') {
                statusElement.addClass('bg-warning text-white text-tunai px-2 py-1 rounded'); // Status pembayaran Tunai (warna kuning)
            } else if (status === 'Hutang') {
                statusElement.addClass('bg-danger text-white text-hutang px-2 py-1 rounded'); // Status pembayaran Hutang (warna merah)
            }

            // Mengganti isi sel dengan elemen status
            statusCell.empty().append(statusElement);
        }
        });

        $('.table-supplier').DataTable();
        table1 = $('.table-detail').DataTable({
            processing: true,
            bSort: false,
            dom: 'Brt',
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false, className: 'text-center'},
                {data: 'kode_produk', className: 'text-center'},
                {data: 'nama_produk', className: 'text-center'},
                {data: 'harga_beli', className: 'text-center'},
                {data: 'jumlah', className: 'text-center'},
                {data: 'subtotal', className: 'text-center'},
            ]
        })
    });

    function addForm() {
        $('#modal-supplier').modal('show');
    }

    function showDetail(url) {
        $('#modal-detail').modal('show');

        table1.ajax.url(url);
        table1.ajax.reload();
    }

    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload();
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                    return;
                });
        }
    }
</script>
@endpush
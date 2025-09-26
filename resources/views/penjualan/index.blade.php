@extends('layouts.master')

@section('title')
    Daftar Penjualan
@endsection

<style>
    .table thead th {
        background-color: #cccccc !important; /* header abu-abu */
        color: #333;
    }

    table.table th, table.table td {
        border: 2px solid #dee2e6 !important;
    }

    .hover-scale:hover {
        transform: scale(1.1);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .table-font-lg {
        font-size: 16px; /* sesuaikan ukuran sesuai kebutuhan */
    }

    .btn-container {
    margin-left: 10px;  /* Add space to the left */
    margin-right: 10px; /* Add space to the right */
    }

    .btn-group .btn {
        margin: 0 5px;  /* Add space between the buttons */
    }
</style>

@section('breadcrumb')
    @parent
    <li class="active">Daftar Penjualan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered table-penjualan">
                    <thead>
                    <th class="text-center" width="5%">No</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Kode Member</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-center">Total Harga</th>
                        <th class="text-center">Diskon</th>
                        <th class="text-center">Total Bayar</th>
                        <th class="text-center">Kasir</th>
                        <th class="text-center" width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('penjualan.detail')
@endsection

@push('scripts')
<script>
    let table, table1;
    let db;
    const dbName = 'PenjualanDB';
    const storeName = 'penjualans';

    // ✅ Inisialisasi IndexedDB
    const request = indexedDB.open(dbName, 1);
    request.onupgradeneeded = function (event) {
        db = event.target.result;
        if (!db.objectStoreNames.contains(storeName)) {
            db.createObjectStore(storeName, { keyPath: 'id_penjualan' }); // gunakan key yang sesuai
        }
    };
    request.onsuccess = function (event) {
        db = event.target.result;
    };

    function saveToIndexedDB(dataArray) {
        if (!db) return;
        const tx = db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);
        store.clear();
        dataArray.forEach(item => {
            store.put(item);
        });
    }

    function loadFromIndexedDB(callback) {
        const tx = db.transaction(storeName, 'readonly');
        const store = tx.objectStore(storeName);
        const request = store.getAll();
        request.onsuccess = () => {
            callback(request.result);
        };
    }

    $(function () {
        const isOnline = navigator.onLine;

        table = $('.table-penjualan').DataTable({
            responsive: true,
            processing: true,
            serverSide: false, // jadikan false agar bisa pakai data dari IndexedDB
            autoWidth: false,
            ajax: function (data, callback, settings) {
                if (isOnline) {
                    $.ajax({
                        url: '{{ route('penjualan.data') }}',
                        dataType: 'json',
                        success: function (json) {
                            saveToIndexedDB(json.data);
                            callback(json);
                        },
                        error: function () {
                            alert('Gagal memuat data dari server.');
                        }
                    });
                } else {
                    loadFromIndexedDB(function (data) {
                        const offlineData = {
                            data: data.map((item, index) => ({
                                ...item,
                                DT_RowIndex: index + 1
                            }))
                        };
                        callback(offlineData);
                    });
                }
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false, className: 'text-center'},
                {data: 'tanggal', className: 'text-center'},
                {data: 'kode_member', className: 'text-center'},
                {data: 'total_item', className: 'text-center'},
                {data: 'total_harga', className: 'text-center'},
                {data: 'diskon', className: 'text-center'},
                {data: 'bayar', className: 'text-center'},
                {data: 'kasir', className: 'text-center'},
                {data: 'aksi', searchable: false, sortable: false, className: 'text-center'},
            ]
        });

        table1 = $('.table-detail').DataTable({
            processing: true,
            bSort: false,
            dom: 'Brt',
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false, className: 'text-center'},
                {data: 'kode_produk', className: 'text-center'},
                {data: 'nama_produk', className: 'text-center'},
                {data: 'harga_jual', className: 'text-center'},
                {data: 'jumlah', className: 'text-center'},
                {data: 'subtotal', className: 'text-center'},
            ]
        });
    });

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

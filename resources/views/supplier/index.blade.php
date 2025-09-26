@extends('layouts.master')

@section('title')
    Daftar Supplier
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Supplier</li>
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
                <button onclick="addForm('{{ route('supplier.store') }}')" class="btn btn-success btn-sm btn-flat"><i class="fa fa-plus-circle"></i> Tambah</button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
                    <thead>
                        <th class="text-center" width="5%">No</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center">Telepon</th>
                        <th class="text-center">Alamat</th>
                        <th class="text-center" width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('supplier.form')
@endsection

@push('scripts')
<script>
    let table;
    let db;
    const dbName = 'SupplierDB';
    const storeName = 'suppliers';

    // ✅ Inisialisasi IndexedDB
    const request = indexedDB.open(dbName, 1);
    request.onupgradeneeded = function (event) {
        db = event.target.result;
        if (!db.objectStoreNames.contains(storeName)) {
            db.createObjectStore(storeName, { keyPath: 'id_supplier' });
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

        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: false,
            autoWidth: false,
            ajax: function (data, callback, settings) {
                if (isOnline) {
                    $.ajax({
                        url: '{{ route('supplier.data') }}',
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
                {data: 'nama', className: 'text-center'},
                {data: 'telepon', className: 'text-center'},
                {data: 'alamat', className: 'text-center'},
                {data: 'aksi', searchable: false, sortable: false, className: 'text-center'},
            ]
        });

        $('#modal-form').validator().on('submit', function (e) {
            if (! e.preventDefault()) {
                $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
                    .done((response) => {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                    })
                    .fail((errors) => {
                        alert('Tidak dapat menyimpan data');
                        return;
                    });
            }
        });
    });

    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Supplier');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama]').focus();
    }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Supplier');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=nama]').focus();

        $.get(url)
            .done((response) => {
                $('#modal-form [name=nama]').val(response.nama);
                $('#modal-form [name=telepon]').val(response.telepon);
                $('#modal-form [name=alamat]').val(response.alamat);
            })
            .fail((errors) => {
                alert('Tidak dapat menampilkan data');
                return;
            });
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

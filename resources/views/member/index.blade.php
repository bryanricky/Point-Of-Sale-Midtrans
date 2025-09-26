@extends('layouts.master')

@section('title')
    Daftar Member
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Member</li>
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
                <button onclick="addForm('{{ route('member.store') }}')" class="btn btn-success btn-sm btn-flat"><i class="fa fa-plus-circle"></i> Tambah</button>
                <button onclick="cetakMember('{{ route('member.cetak_member') }}')" class="btn btn-info btn-sm btn-flat"><i class="fa fa-id-card"></i> Cetak Member</button>
            </div>
            <div class="box-body table-responsive">
                <form action="" method="post" class="form-member">
                    @csrf
                    <table class="table table-stiped table-bordered">
                        <thead>
                            <th width="5%">
                                <input type="checkbox" name="select_all" id="select_all">
                            </th>
                            <th class="text-center" width="5%">No</th>
                            <th class="text-center">Kode</th>
                            <th class="text-center">Nama</th>
                            <th class="text-center">Telepon</th>
                            <th class="text-center">Alamat</th>
                            <th class="text-center" width="15%"><i class="fa fa-cog"></i></th>
                        </thead>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

@includeIf('member.form')
@endsection

@push('scripts')
<script>
    let table;
    let db;
    const dbName = 'MemberDB';
    const storeName = 'members';

    // ✅ Inisialisasi IndexedDB
    const request = indexedDB.open(dbName, 1);
    request.onupgradeneeded = function (event) {
        db = event.target.result;
        if (!db.objectStoreNames.contains(storeName)) {
            db.createObjectStore(storeName, { keyPath: 'id_member' });
        }
    };
    request.onsuccess = function (event) {
        db = event.target.result;
    };

    // ✅ Simpan data ke IndexedDB
    function saveToIndexedDB(dataArray) {
        if (!db) return;
        const tx = db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);
        store.clear(); // Bersihkan data lama
        dataArray.forEach((item) => {
            store.put(item);
        });
    }

    // ✅ Ambil data dari IndexedDB
    function loadFromIndexedDB(callback) {
        const tx = db.transaction(storeName, 'readonly');
        const store = tx.objectStore(storeName);
        const request = store.getAll();
        request.onsuccess = function () {
            callback(request.result);
        };
    }

    $(function () {
        const isOnline = navigator.onLine;

        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: false, // disable server-side jika offline
            autoWidth: false,
            ajax: function (data, callback, settings) {
                if (isOnline) {
                    $.ajax({
                        url: '{{ route('member.data') }}',
                        dataType: 'json',
                        success: function (json) {
                            // Simpan data ke IndexedDB untuk offline
                            saveToIndexedDB(json.data);

                            callback(json);
                        },
                        error: function () {
                            alert('Gagal memuat data dari server.');
                        }
                    });
                } else {
                    // Offline mode
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
                {data: 'select_all', searchable: false, sortable: false, className: 'text-center'},
                {data: 'DT_RowIndex', searchable: false, sortable: false, className: 'text-center'},
                {data: 'kode_member', className: 'text-center'},
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

        $('[name=select_all]').on('click', function () {
            $(':checkbox').prop('checked', this.checked);
        });
    });

    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Member');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama]').focus();
    }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Member');

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

    function cetakMember(url) {
        if ($('input:checked').length < 1) {
            alert('Pilih data yang akan dicetak');
            return;
        } else {
            $('.form-member')
                .attr('target', '_blank')
                .attr('action', url)
                .submit();
        }
    }
</script>
@endpush

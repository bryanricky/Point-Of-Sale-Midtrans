@extends('layouts.master')

@section('title')
    Daftar Pelanggan
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Pelanggan</li>
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

    #imageModal .modal-dialog {
        max-width: 90%;
        margin: 0 auto;
        height: auto;
    }

    #imageModal .modal-content {
        background: transparent; /* Hilangkan background putih */
        border: none;            /* Opsional: hilangkan border bawaan */
        box-shadow: none;        /* Opsional: hilangkan shadow bawaan */
    }

    #imageModal .modal-body {
        padding: 0;
        background: transparent; /* Hilangkan background putih */
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
    }

    #imageModal img {
        width: 100%;
        max-height: 90vh;
        object-fit: contain;
        margin: 0 auto;
    }

    table.table td {
        text-align: center !important;
        vertical-align: middle !important;
    }

</style>

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addForm('{{ route('pelanggan.store') }}')" class="btn btn-success btn-sm btn-flat"><i class="fa fa-plus-circle"></i> Tambah</button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
                    <thead>
                        <th class="text-center" width="5%">No</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center">Telepon</th>
                        <th class="text-center">Alamat</th>
                        <th class="text-center">Foto</th>
                        <th class="text-center" width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('pelanggan.form')

<!-- Modal Gambar -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <img id="modalImage" src="" class="img-fluid rounded" alt="Foto Pelanggan">
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
    let table;
    let db;
    const dbName = 'PelangganDB';
    const storeName = 'pelanggans';

    // ✅ Inisialisasi IndexedDB
    const request = indexedDB.open(dbName, 1);
    request.onupgradeneeded = function (event) {
        db = event.target.result;
        if (!db.objectStoreNames.contains(storeName)) {
            db.createObjectStore(storeName, { keyPath: 'id_pelanggan' });
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

    function showImageModal(url) {
        $('#modalImage').attr('src', url);
        $('#imageModal').modal('show');
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
                        url: '{{ route('pelanggan.data') }}',
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
                {
                    data: 'path_foto',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return data;
                    }
                },
                {data: 'aksi', searchable: false, sortable: false, className: 'text-center'},
            ]
        });

        $('#modal-form').validator().on('submit', function (e) {
            if (! e.preventDefault()) {
                let form = $('#modal-form form')[0];
                let formData = new FormData(form);

                $.ajax({
                    url: $('#modal-form form').attr('action'),
                    method: $('#modal-form [name=_method]').val() === 'put' ? 'POST' : 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                    },
                    error: function (errors) {
                        alert('Tidak dapat menyimpan data');
                    }
                });
            }
        });
    });

    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Pelanggan');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama]').focus();
        $('#preview-foto').hide();
    }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Pelanggan');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=nama]').focus();

        $.get(url)
            .done((response) => {
                $('#modal-form [name=nama]').val(response.nama);
                $('#modal-form [name=telepon]').val(response.telepon);
                $('#modal-form [name=alamat]').val(response.alamat);
                if (response.path_foto) {
                    $('#preview-foto').attr('src', '/' + response.path_foto).show();
                } else {
                    $('#preview-foto').hide();
                }
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

    function previewFoto(input) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $('#preview-foto').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush

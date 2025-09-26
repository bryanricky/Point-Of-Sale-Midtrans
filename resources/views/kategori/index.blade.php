@extends('layouts.master')

@section('title')
    Daftar Kategori Produk
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Kategori</li>
@endsection

<style>

    table.table th, table.table td {
        border: 2px solid #dee2e6 !important;
    }

    .table thead th {
        background-color: #cccccc !important; /* header abu-abu */
        color: #333;
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
    
    .btn-medium {
        padding: 8px 16px;
        font-size: 16px;
        line-height: 1.5;
        border-radius: 4px;
    }
</style>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addForm('{{ route('kategori.store') }}')" class="btn btn-success btn-flat btn-medium"><i class="fa fa-plus-circle"> Tambah</i></button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <th width="5%">No</th>
                        <th>Kategori</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>    
                </table>
            </div>
        </div>       
    </div>
</div>

@includeIf('kategori.form')
@endsection

@push('scripts')
<script>
let table;
let db;

// ✅ Inisialisasi IndexedDB
const request = indexedDB.open('KategoriDB', 1);

request.onupgradeneeded = function (event) {
    db = event.target.result;
    if (!db.objectStoreNames.contains('kategori')) {
        db.createObjectStore('kategori', { keyPath: 'id' });
    }
};

request.onsuccess = function (event) {
    db = event.target.result;
    initTable();
};

function saveToIndexedDB(data) {
    const tx = db.transaction('kategori', 'readwrite');
    const store = tx.objectStore('kategori');
    store.clear(); // Hapus data lama
    data.forEach(item => store.put(item));
}

function getFromIndexedDB(callback) {
    const tx = db.transaction('kategori', 'readonly');
    const store = tx.objectStore('kategori');
    const request = store.getAll();

    request.onsuccess = function () {
        callback(request.result);
    };

    request.onerror = function () {
        alert('Gagal mengambil data dari IndexedDB');
    };
}

function initTable() {
    if (navigator.onLine) {
        // ✅ MODE ONLINE
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('kategori.data') }}',
                dataSrc: function (json) {
                    saveToIndexedDB(json.data); // Simpan ke IndexedDB
                    return json.data;
                }
            },
            columns: [
                { data: 'DT_RowIndex', className: 'text-center', searchable: false, sortable: false },
                { data: 'nama_kategori' },
                { data: 'aksi', className: 'text-center', searchable: false, sortable: false },
            ]
        });
    } else {
        // ✅ MODE OFFLINE
        getFromIndexedDB(function (data) {
            if (data.length > 0) {
                table = $('.table').DataTable({
                    responsive: true,
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    data: data,
                    columns: [
                        { data: 'DT_RowIndex', className: 'text-center', searchable: false, sortable: false },
                        { data: 'nama_kategori' },
                        { data: 'aksi', className: 'text-center', searchable: false, sortable: false },
                    ]
                });
            } else {
                alert('Tidak ada data kategori tersedia secara offline.');
            }
        });
    }

    // ✅ Nonaktifkan tombol aksi saat offline
    $('.table').on('click', '.btn', function (e) {
        if (!navigator.onLine) {
            alert('Fitur ini tidak tersedia saat offline.');
            e.preventDefault();
        }
    });
}

// ✅ Form handling tetap
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

// ✅ Fungsi tambahan tetap
function addForm(url) {
    $('#modal-form').modal('show');
    $('#modal-form .modal-title').text('Tambah Kategori');

    $('#modal-form form')[0].reset();
    $('#modal-form form').attr('action', url);
    $('#modal-form [name=_method]').val('post');
    $('#modal-form [name=nama_kategori]').focus();
}

function editForm(url) {
    $('#modal-form').modal('show');
    $('#modal-form .modal-title').text('Edit Kategori');

    $('#modal-form form')[0].reset();
    $('#modal-form form').attr('action', url);
    $('#modal-form [name=_method]').val('put');
    $('#modal-form [name=nama_kategori]').focus();

    $.get(url)
        .done((response) => {
            $('#modal-form [name=nama_kategori]').val(response.nama_kategori);
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

@extends('layouts.master')

@section('title')
    Daftar Produk
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Produk</li>
@endsection

<style>
    table.table th, table.table td {
        border: 2px solid #dee2e6 !important;
    }

    /* Center the table body text */
    table.table td {
        text-align: center !important;
        vertical-align: middle !important;
    }

    .hover-scale:hover {
        transform: scale(1.1);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .btn-container {
    margin-left: 5px;  /* Add space to the left */
    margin-right: 5px; /* Add space to the right */
    }

    .btn-group .btn {
        margin: 5px;  /* Add space between the buttons */
    }

    /* Center the header text */
    table.table th {
        text-align: center;
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

</style>


@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default shadow-sm rounded-lg">
            <div class="panel-heading d-flex justify-content-between align-items-center bg-primary text-white p-3 rounded-top">
                
                <div class="d-flex gap-2">
                    <button onclick="addForm('{{ route('produk.store') }}')" class="btn btn-info btn-sm"><i class="fa fa-plus-circle"></i> Tambah</button>
                    <button onclick="deleteSelected('{{ route('produk.delete_selected') }}')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Hapus</button>
                    <button onclick="cetakBarcode('{{ route('produk.cetak_barcode') }}')" class="btn btn-warning btn-sm"><i class="fa fa-barcode"></i> Cetak Barcode</button>
                </div>

            </div>
            <div class="row px-3 py-2">
            @if($produkKosong->count() > 0)
            <div class="col-md-6">
                <div class="alert alert-danger" style="max-height: 200px; overflow-y: auto;">
                    <strong>Perhatian!</strong> Stok kosong untuk produk berikut:
                    <ul class="mb-0">
                        @foreach($produkKosong as $produk)
                            <li>{{ $produk->nama_produk }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            @if($produkHampirHabis->count())
            <div class="col-md-6">
                <div class="alert alert-warning" style="max-height: 200px; overflow-y: auto;">
                    <strong>Peringatan!</strong> Ada {{ $produkHampirHabis->count() }} produk yang hampir habis stoknya:
                    <ul class="mb-0">
                        @foreach($produkHampirHabis as $produk)
                            <li>{{ $produk->nama_produk }} (sisa {{ $produk->stok }})</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>

            <div class="panel-body p-3 bg-white border rounded-bottom table-responsive">
                <form action="" method="post" class="form-produk">
                    @csrf
                    <table class="table table-bordered border-dark table-striped table-hover shadow-sm">
                        <thead class="text-white text-center" style="background-color:  #cccccc;">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" name="select_all" id="select_all">
                                </th>
                                <th width="5%">No</th>
                                <th width="8%">Kode</th>
                                <th width="8%">Nama</th>
                                <th width="8%">Kategori</th>
                                <th width="8%">Merk</th>
                                <th width="8%">Harga Beli</th>
                                <th width="8%">Harga Jual</th>
                                <th width="8%">Diskon</th>
                                <th width="5%">Stok</th>
                                <th width="8%">Foto</th>
                                <th width="8%"><i class="fa fa-cog"></i></th>
                            </tr>
                        </thead>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk menampilkan gambar -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img id="popupImage" src="" class="img-fluid" alt="Gambar Produk">
      </div>
    </div>
  </div>
</div>

@includeIf('produk.form')
@endsection

@push('scripts')
<script>
   function showImagePopup(imageUrl) {
    $('#popupImage').attr('src', imageUrl);
    $('#imageModal').modal('show');
}

let table;
let db;

// ✅ Inisialisasi IndexedDB
const request = indexedDB.open('ProdukDB', 1);
request.onupgradeneeded = function (event) {
    db = event.target.result;
    if (!db.objectStoreNames.contains('produk')) {
        db.createObjectStore('produk', { keyPath: 'id_produk' });
    }
};

request.onsuccess = function (event) {
    db = event.target.result;

    if (navigator.onLine) {
        console.log("🌐 Online - Memuat DataTable dan simpan ke IndexedDB");

        // ✅ Inisialisasi DataTable
        initDataTable();

        // ✅ Ambil data untuk disimpan offline
        $.ajax({
            url: '{{ route('produk.data') }}',
            method: 'GET',
            success: function (response) {
                const data = response.data || response;
                console.log('📦 Data dari server:', data);
                saveToIndexedDB(data);
            },
            error: function () {
                console.warn('❌ Gagal fetch data dari server');
            }
        });

    } else {
        console.log("📴 Offline - Load data dari IndexedDB");
        loadFromIndexedDB(displayOfflineData);
    }
};

// ✅ Tambahkan DI BAWAH sini (atau di akhir script JS kamu)
window.addEventListener('load', () => {
    if (!navigator.onLine) {
        console.log('🔌 Tidak terhubung - fallback ke IndexedDB');
        loadFromIndexedDB(displayOfflineData);
    }
});

// ⚡ Fallback saat koneksi terputus setelah halaman sudah terbuka
window.addEventListener('offline', () => {
    console.warn('📴 Koneksi internet terputus - menampilkan data dari IndexedDB');
    loadFromIndexedDB(displayOfflineData);
});

// ✅ Simpan data ke IndexedDB
function saveToIndexedDB(data) {
    const tx = db.transaction('produk', 'readwrite');
    const store = tx.objectStore('produk');

    store.clear(); // Hapus data lama
    data.forEach((item) => {
        try {
            store.put(item);
        } catch (e) {
            console.error('❌ Gagal simpan item:', item, e);
        }
    });

    tx.oncomplete = () => console.log('✅ Data berhasil disimpan ke IndexedDB');
    tx.onerror = (e) => console.error('❌ Error IndexedDB:', e.target.error);
}

// ✅ Load data dari IndexedDB
function loadFromIndexedDB(callback) {
    const tx = db.transaction('produk', 'readonly');
    const store = tx.objectStore('produk');
    const items = [];

    store.openCursor().onsuccess = function (event) {
        const cursor = event.target.result;
        if (cursor) {
            items.push(cursor.value);
            cursor.continue();
        } else {
            console.log('📁 Data ditemukan di IndexedDB:', items);
            callback(items);
        }
    };
}

// ✅ Tampilkan data offline ke tabel
function displayOfflineData(data) {
    if ($.fn.DataTable.isDataTable('.table')) {
        $('.table').DataTable().destroy();
    }

    $('.table').DataTable({
        data: data,
        columns: [
            {
                data: null,
                render: () => '<input type="checkbox">',
                className: 'text-center'
            },
            {
                data: null,
                render: (data, type, row, meta) => meta.row + 1,
                className: 'text-center'
            },
            { data: 'kode_produk', className: 'text-center' },
            { data: 'nama_produk', className: 'text-center' },
            { data: 'nama_kategori', className: 'text-center' },
            { data: 'merk', className: 'text-center' },
            { data: 'harga_beli', className: 'text-center' },
            { data: 'harga_jual', className: 'text-center' },
            { data: 'diskon', className: 'text-center' },
            { data: 'stok', className: 'text-center' },
            {
                data: 'path_foto',
                render: (data) => `<img src="${data}" width="50" onclick="showImagePopup('${data}')">`,
                className: 'text-center'
            },
            {
                data: null,
                render: () => `<button class="btn btn-danger btn-sm">Aksi</button>`,
                className: 'text-center'
            }
        ]
    });
}


// ✅ Inisialisasi DataTable hanya saat online
function initDataTable() {
    table = $('.table').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: {
            url: '{{ route('produk.data') }}',
        },
        columns: [
            {data: 'select_all', searchable: false, sortable: false, className: 'text-center'},
            {data: 'DT_RowIndex', searchable: false, sortable: false, className: 'text-center'},
            {data: 'kode_produk', className: 'text-center'},
            {data: 'nama_produk', className: 'text-center'},
            {data: 'nama_kategori', className: 'text-center'},
            {data: 'merk', className: 'text-center'},
            {data: 'harga_beli', className: 'text-center'},
            {data: 'harga_jual', className: 'text-center'},
            {data: 'diskon', className: 'text-center'},
            {data: 'stok', className: 'text-center'},
            {data: 'path_foto', className: 'text-center'},
            {data: 'aksi', searchable: false, sortable: false, className: 'text-center'}
        ]
    });
}

// ✅ Form dan checkbox handler
$(function () {
    $('#modal-form').validator().on('submit', function (e) {
        if (!e.isDefaultPrevented()) return;

        e.preventDefault();
        let form = $('#modal-form form')[0];
        let formData = new FormData(form);

        $.ajax({
            url: $('#modal-form form').attr('action'),
            method: $('#modal-form [name=_method]').val() === 'put' ? 'POST' : 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function () {
                $('#modal-form').modal('hide');
                if (table) table.ajax.reload();
            },
            error: function () {
                alert('❌ Tidak dapat menyimpan data');
            }
        });
    });

    $('[name=select_all]').on('click', function () {
        $(':checkbox').prop('checked', this.checked);
    });

    $('#btn-hapus-foto').on('click', function () {
        hapusFoto();
    });
});


    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Produk');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama_produk]').focus();

        // Reset preview foto dan hapus flag
        $('#preview-foto').attr('src', '').hide();
        $('#btn-hapus-foto').hide();
        $('#hapus_foto').val('0');
    }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Produk');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=nama_produk]').focus();

        $.get(url)
            .done((response) => {
                $('#modal-form [name=nama_produk]').val(response.nama_produk);
                $('#modal-form [name=id_kategori]').val(response.id_kategori);
                $('#modal-form [name=merk]').val(response.merk);
                $('#modal-form [name=harga_beli]').val(response.harga_beli);
                $('#modal-form [name=harga_jual]').val(response.harga_jual);
                $('#modal-form [name=diskon]').val(response.diskon);
                $('#modal-form [name=stok]').val(response.stok);

                if (response.path_foto) {
                    $('#preview-foto').attr('src', '/' + response.path_foto).show();
                    $('#btn-hapus-foto').show();
                    $('#hapus_foto').val('0'); // reset flag hapus foto
                } else {
                    $('#preview-foto').attr('src', '').hide();
                    $('#btn-hapus-foto').hide();
                    $('#hapus_foto').val('0');
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

    function deleteSelected(url) {
        if ($('input:checked').length > 1) {
            if (confirm('Yakin ingin menghapus data terpilih?')) {
                $.post(url, $('.form-produk').serialize())
                    .done((response) => {
                        table.ajax.reload();
                    })
                    .fail((errors) => {
                        alert('Tidak dapat menghapus data');
                        return;
                    });
            }
        } else {
            alert('Pilih data yang akan dihapus');
            return;
        }
    }

    function cetakBarcode(url) {
        if ($('input:checked').length < 1) {
            alert('Pilih data yang akan dicetak');
            return;
        } else if ($('input:checked').length < 3) {
            alert('Pilih minimal 3 data untuk dicetak');
            return;
        } else {
            $('.form-produk')
                .attr('target', '_blank')
                .attr('action', url)
                .submit();
        }
    }

    function previewFoto(input) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $('#preview-foto').attr('src', e.target.result).show();
                $('#btn-hapus-foto').show();
                $('#hapus_foto').val('0'); // reset flag hapus saat pilih foto baru
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function hapusFoto() {
        $('#preview-foto').attr('src', '').hide();
        $('input[name=path_foto]').val('');
        $('#btn-hapus-foto').hide();
        $('#hapus_foto').val('1'); // tandai foto akan dihapus saat submit
    }

     // Saat tombol hapus foto diklik, tampilkan modal konfirmasi
    $('#btn-hapus-foto').on('click', function () {
        $('#confirmDeleteFotoModal').modal('show');
    });

    // Jika user yakin hapus foto di modal konfirmasi
    $('#confirmDeleteFotoBtn').on('click', function () {
        // Sembunyikan modal konfirmasi
        $('#confirmDeleteFotoModal').modal('hide');
        
        // Kosongkan preview foto
        $('#preview-foto').attr('src', '').hide();
        $('#btn-hapus-foto').hide();

        // Set flag hapus foto jadi 1 supaya backend tahu foto dihapus
        $('#hapus_foto').val('1');

        // Kosongkan input file (agar tidak ikut terupload)
        $('input[name=path_foto]').val('');
    });
</script>
@endpush

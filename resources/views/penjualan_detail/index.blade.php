@extends('layouts.master')

@section('title')
    Transaksi Penjualan
@endsection

@push('css')
<style>
    .tampil-bayar {
        font-size: 5em;
        text-align: center;
        height: 100px;
    }

    .tampil-terbilang {
        padding: 10px;
        background: #f0f0f0;
    }

    .table-penjualan tbody tr:last-child {
        display: none;
    }

    @media(max-width: 768px) {
        .tampil-bayar {
            font-size: 3em;
            height: 70px;
            padding-top: 5px;
        }
    }

    .shortcut-title {
        font-weight: bold;
        border-bottom: 3px solid #fff;
        padding: 12px 0;
        margin-bottom: 12px;
    }

    .shortcut-list {
        display: flex;
        flex-wrap: wrap;
        padding-left: 0;
        margin-bottom: 0;
        gap: 10px;
    }

    .shortcut-list li {
        flex: 0 0 32%; /* 3 kolom */
        display: flex;
        align-items: flex-start;
        padding: 0 8px; /* Jarak kiri-kanan antar kolom */
        gap: 8px;
        box-sizing: border-box;
    }

    .shortcut-key {
        min-width: 110px; /* Lebih besar agar rata dengan titik dua */
        font-weight: bold;
        display: inline-block;
        text-align: right; /* sejajarkan ke kanan */
    }

    .shortcut-desc {
        flex: 1;
        padding-left: 5px;
    }

    .shortcut-info {
        background-color: #d3d3d3;
        color: #000;
        width: 100%;
        max-width: none; /* tambahkan ini agar override max-width default */
        flex: 1; /* tambahkan ini agar isi melebar sesuai parent */
        padding-bottom: 10px; /* 👈 Tambahan ruang bawah */
    }
</style>
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Transaksi Penjaualn</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body">
                    
                <form class="form-produk">
                    @csrf
                    <div class="form-group row">
                        <label for="kode_produk" class="col-lg-2">Kode Produk</label>
                        <div class="col-lg-5">
                            <div class="input-group">
                                <input type="hidden" name="id_penjualan" id="id_penjualan" value="{{ $id_penjualan }}">
                                <input type="hidden" name="id_produk" id="id_produk">
                                <input type="text" class="form-control" name="kode_produk" id="kode_produk" autofocus>
                                <span class="input-group-btn">
                                    <button onclick="tampilProduk()" class="btn btn-info btn-flat" type="button"><i class="fa fa-arrow-right"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-stiped table-bordered table-penjualan" style="background-color: #f0f0f0;">
                    <thead style="background-color: #d3d3d3;"> <!-- Latar belakang abu-abu pada header tabel -->
                        <tr>
                            <th width="5%" style="text-align: center;">No</th>
                            <th style="text-align: center;">Kode</th>
                            <th style="text-align: center;">Nama</th>
                            <th style="text-align: center;">Merk</th>
                            <th style="text-align: center;">Harga</th>
                            <th width="15%" style="text-align: center;">Jumlah</th>
                            <th style="text-align: center;">Diskon</th>
                            <th style="text-align: center;">Subtotal</th>
                            <th width="15%" style="text-align: center;"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                </table>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="tampil-bayar bg-primary text-white p-4 mb-10 rounded" style="font-size: 5em; font-weight: bold; text-align: center;">
                            <!-- Nilai total akan muncul di sini -->
                        </div>
                        <div class="tampil-terbilang bg-light text-dark p-2 rounded" style="font-size: 3rem; font-family: 'Arial', sans-serif; background-color: rgba(169, 169, 169, 0.4);">
                            <!-- Terbilang total akan muncul di sini -->
                        </div>
                        <div class="mt-5 pt-4 pb-4">
                            <div class="shortcut-info p-4 rounded shadow-sm w-100">

                                <h5 class="shortcut-title text-center d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-keyboard-o"></i> Informasi Shortcut Keyboard</span>
                                    <button id="toggleShortcut" class="btn btn-sm btn-light" style="border: none; margin-left: 12px;">
                                        <i id="iconToggle" class="fa fa-chevron-down"></i>
                                    </button>
                                </h5>
                                <ul class="shortcut-list" id="shortcutContent">
                                    <li><span class="shortcut-key"><kbd>Ctrl</kbd> + <kbd>Q</kbd></span>: <span class="shortcut-desc">Untuk input jumlah produk</span></li>
                                    <li><span class="shortcut-key"><kbd>Tab</kbd></span>: <span class="shortcut-desc">Untuk pindah ke input berikutnya</span></li> 
                                    <li><span class="shortcut-key"><kbd>F1</kbd></span>: <span class="shortcut-desc">Untuk menampilkan daftar produk</span></li>
                                    <li><span class="shortcut-key"><kbd>Ctrl</kbd> + <kbd>B</kbd></span>: <span class="shortcut-desc">Untuk fokus ke kolom diterima</span></li>
                                    <li><span class="shortcut-key"><kbd>Ctrl</kbd> + <kbd>M</kbd></span>: <span class="shortcut-desc">Untuk fokus ke metode pembayaran</span></li>                             
                                    <li><span class="shortcut-key"><kbd>F2</kbd></span>: <span class="shortcut-desc">Untuk menampilkan daftar member</span></li>      
                                    <li><span class="shortcut-key"><kbd>Ctrl</kbd> + <kbd>S</kbd></span>: <span class="shortcut-desc">Untuk simpan transaksi</span></li> 
                                    <li><span class="shortcut-key"><kbd>Shift</kbd> + <kbd>Tab</kbd></span>: <span class="shortcut-desc">Untuk kembali ke input sebelumnya</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <form action="{{ route('transaksi.simpan') }}" class="form-penjualan" method="post">
                            @csrf
                            <input type="hidden" name="id_penjualan" value="{{ $id_penjualan }}">
                            <input type="hidden" name="total" id="total">
                            <input type="hidden" name="total_item" id="total_item">
                            <input type="hidden" name="bayar" id="bayar">
                            <input type="hidden" name="id_member" id="id_member" value="{{ $memberSelected->id_member }}">

                            <div class="form-group row">
                                <label for="totalrp" class="col-lg-2 control-label">Total</label>
                                <div class="col-lg-8">
                                    <input type="text" id="totalrp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="kode_member" class="col-lg-2 control-label">Member</label>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="kode_member" value="{{ $memberSelected->kode_member }}">
                                        <span class="input-group-btn">
                                            <button onclick="tampilMember()" class="btn btn-info btn-flat" type="button"><i class="fa fa-arrow-right"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- Pilihan Metode Pembayaran -->
                            <div class="form-group row">
                                <label for="metode_pembayaran" class="col-lg-2 control-label">Metode Pembayaran</label>
                                <div class="col-lg-8">
                                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-control">
                                        <option value="">-- Pilih Metode --</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="non_transfer">Tunai</option>
                                        <!-- Tambahkan metode lainnya jika diperlukan -->
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="diskon" class="col-lg-2 control-label">Diskon</label>
                                <div class="col-lg-8">
                                    <input type="number" name="diskon" id="diskon" class="form-control" 
                                        value="{{ ! empty($memberSelected->id_member) ? $diskon : 0 }}" 
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bayar" class="col-lg-2 control-label">Bayar</label>
                                <div class="col-lg-8">
                                    <input type="text" id="bayarrp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="diterima" class="col-lg-2 control-label">Diterima</label>
                                <div class="col-lg-8">
                                    <input type="number" id="diterima" class="form-control" name="diterima" value="{{ $penjualan->diterima ?? 0 }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="kembali" class="col-lg-2 control-label">Kembali</label>
                                <div class="col-lg-8">
                                    <input type="text" id="kembali" name="kembali" class="form-control" value="0" readonly>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary btn-sm btn-flat pull-right btn-simpan"><i class="fa fa-floppy-o"></i> Simpan Transaksi</button>
            </div>

        </div>
    </div>
</div>

@includeIf('penjualan_detail.produk')
@includeIf('penjualan_detail.member')
@endsection

@push('scripts')
<script>
    let table, table2;
    let isOffline = !navigator.onLine;

    $(function () {
        $('body').addClass('sidebar-collapse');

        if (isOffline) {
            table = $('.table-penjualan').DataTable({
                responsive: true,
                processing: true,
                serverSide: false,
                autoWidth: false,
                data: [],
                columns: [
                    {data: 'DT_RowIndex', className: 'text-center'},
                    {data: 'kode_produk', className: 'text-center'},
                    {data: 'nama_produk', className: 'text-center'},
                    {data: 'merk', className: 'text-center'},
                    {data: 'harga_jual', className: 'text-center'},
                    {data: 'jumlah', className: 'text-center'},
                    {data: 'diskon', className: 'text-center'},
                    {data: 'subtotal', className: 'text-center'},
                    {data: 'aksi', className: 'text-center'},
                ],
                dom: 'Brt',
                bSort: false,
                paging: false
            });

            tampilkanDataOffline(); // Ambil dari IndexedDB / localStorage
        } else {
            table = $('.table-penjualan').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('transaksi.data', $id_penjualan) }}',
                },
                columns: [
                    {data: 'DT_RowIndex', className: 'text-center', searchable: false, sortable: false},
                    {data: 'kode_produk', className: 'text-center'},
                    {data: 'nama_produk', className: 'text-center'},
                    {data: 'merk', className: 'text-center'},
                    {data: 'harga_jual', className: 'text-center'},
                    {data: 'jumlah', className: 'text-center'},
                    {data: 'diskon', className: 'text-center'},
                    {data: 'subtotal', className: 'text-center'},
                    {data: 'aksi', className: 'text-center', searchable: false, sortable: false},
                ],
                dom: 'Brt',
                bSort: false,
                paginate: false
            }).on('draw.dt', function () {
                loadForm($('#diskon').val());
                setTimeout(() => {
                    $('#diterima').trigger('input');
                }, 300);
            });
        }

        table2 = $('.table-produk').DataTable();

        // Input quantity
        $(document).on('input', '.quantity', function () {
            let id = $(this).data('id');
            let jumlah = parseInt($(this).val());

            if (jumlah < 1) {
                $(this).val(1);
                alert('Jumlah tidak boleh kurang dari 1');
                return;
            }
            if (jumlah > 10000) {
                $(this).val(10000);
                alert('Jumlah tidak boleh lebih dari 10000');
                return;
            }

            if (isOffline) {
                updateJumlahOffline(id, jumlah); // simpan lokal
                tampilkanDataOffline();
            } else {
                $.post(`{{ url('/transaksi') }}/${id}`, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'put',
                    'jumlah': jumlah
                })
                .done(response => {
                    $(this).on('mouseout', function () {
                        table.ajax.reload(() => loadForm($('#diskon').val()));
                    });
                })
                .fail(errors => {
                    alert('Tidak dapat menyimpan data');
                    return;
                });
            }
        });

        $(document).on('input', '#diskon', function () {
            if ($(this).val() == "") $(this).val(0).select();
            loadForm($(this).val());
        });

        $('#diterima').on('input', function () {
            if ($(this).val() == "") $(this).val(0).select();
            loadForm($('#diskon').val(), $(this).val());

            if (isOffline) {
                localStorage.setItem('diterima', $(this).val());
            }
        }).focus(function () {
            $(this).select();
        });

        $('.btn-simpan').on('click', function (e) {
            e.preventDefault();

            if (isOffline) {
                // Simpan transaksi offline
                let data = {
                    diterima: $('#diterima').val(),
                    diskon: $('#diskon').val(),
                    waktu: new Date().toISOString(),
                    detail: ambilDataTabelOffline()
                };
                simpanTransaksiOffline(data);
                alert('Transaksi disimpan secara offline!');
            } else {
                $('.form-penjualan').submit(); // submit ke server
            }
        });
    });

    // ============================
    // 🔽 Fungsi Offline Pendukung
    // ============================

    function simpanTransaksiOffline(data) {
        let offlineTrans = JSON.parse(localStorage.getItem('transaksi_offline')) || [];
        offlineTrans.push(data);
        localStorage.setItem('transaksi_offline', JSON.stringify(offlineTrans));
    }

    function ambilDataTabelOffline() {
        let data = [];
        table.rows().every(function () {
            data.push(this.data());
        });
        return data;
    }

    function tampilkanDataOffline() {
        let data = JSON.parse(localStorage.getItem('detail_penjualan_offline')) || [];
        table.clear().rows.add(data).draw();
        $('#diterima').val(localStorage.getItem('diterima') || 0);
    }

    function updateJumlahOffline(id, jumlah) {
        let data = JSON.parse(localStorage.getItem('detail_penjualan_offline')) || [];
        data = data.map(item => {
            if (item.id == id) item.jumlah = jumlah;
            return item;
        });
        localStorage.setItem('detail_penjualan_offline', JSON.stringify(data));
    }


    function tampilProduk() {
        $('#modal-produk').modal('show');
    }

    function hideProduk() {
        $('#modal-produk').modal('hide');
    }

    function pilihProduk(id, kode) {
        $('#id_produk').val(id);
        $('#kode_produk').val(kode);
        hideProduk();
        tambahProduk();
    }




async function openIndexedDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('POSDB', 1);

        request.onupgradeneeded = function(event) {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('transaksi_sementara')) {
                db.createObjectStore('transaksi_sementara', { keyPath: 'id_penjualan', autoIncrement: true });
            }
        };

        request.onsuccess = event => resolve(event.target.result);
        request.onerror = event => reject(event.target.error);
    });
}

async function simpanOffline(data) {
    const db = await openIndexedDB();
    const tx = db.transaction('transaksi_sementara', 'readwrite');
    const store = tx.objectStore('transaksi_sementara');
    await store.add({ data, waktu: new Date().toISOString() });
}


async function tampilkanDataOffline() {
    const db = await openIndexedDB();
    const tx = db.transaction('transaksi_sementara', 'readonly');
    const store = tx.objectStore('transaksi_sementara');
    const getAll = store.getAll();

    getAll.onsuccess = function () {
        const data = getAll.result;

        // Bersihkan dulu baris sebelumnya
        table.clear();

        data.forEach((item, index) => {
            const formObj = {};
            item.data.forEach(field => formObj[field.name] = field.value);

            table.row.add({
    DT_RowIndex: index + 1,
    kode_produk: formObj.kode_produk || '',
    nama_produk: formObj.nama_produk || '-',
    merk: formObj.merk || '-',
    harga_jual: formObj.harga_jual || '0',
    jumlah: formObj.jumlah || '1',
    diskon: formObj.diskon || '0',
    subtotal: formObj.subtotal || '0',
    aksi: '<span class="text-muted">Offline</span>'
});

        });

        table.draw(false);
    };
}










    async function tambahProduk() {
    const formData = $('.form-produk').serializeArray();

    if (!navigator.onLine) {
    try {
        const db = await openIndexedDB();
        const tx = db.transaction('transaksi_sementara', 'readwrite');
        const store = tx.objectStore('transaksi_sementara');
        const objData = {};
formData.forEach(field => objData[field.name] = field.value);

await store.add({ data: objData, waktu: new Date().toISOString() });

        alert('Transaksi disimpan sementara (offline)');
        $('#kode_produk').val('').focus();

        // render ulang data offline ke tabel
        tampilkanDataOffline();
    } catch (error) {
        alert('Gagal menyimpan offline: ' + error);
    }
    return;
}


    // Jika online
    $.post('{{ route('transaksi.store') }}', $('.form-produk').serialize())
        .done(response => {
            $('#kode_produk').val('').focus();
            table.ajax.reload(() => loadForm($('#diskon').val()));
        })
        .fail(errors => {
            alert('Gagal menyimpan data online.');
        });
}





window.addEventListener('online', async function () {
    try {
        const db = await openIndexedDB();
        const tx = db.transaction('transaksi_sementara', 'readonly');
        const store = tx.objectStore('transaksi_sementara');

        const getAllRequest = store.getAll();
        getAllRequest.onsuccess = async function () {
            const items = getAllRequest.result;
            for (const item of items) {
                await $.post('{{ route('transaksi.store') }}', item.data)
                    .done(() => {
                        console.log('Sinkron berhasil:', item);
                    })
                    .fail(() => {
                        console.error('Gagal sinkron:', item);
                    });
            }

            // Setelah sukses semua, hapus data offline
            const deleteTx = db.transaction('transaksi_sementara', 'readwrite');
            const deleteStore = deleteTx.objectStore('transaksi_sementara');
            deleteStore.clear();
            alert('Transaksi offline berhasil disinkronkan.');
            table.ajax.reload(() => loadForm($('#diskon').val()));
        };
    } catch (error) {
        console.error('Gagal sinkronisasi offline:', error);
    }
});


    function tampilMember() {
        $('#modal-member').modal('show');
    }

    function pilihMember(id, kode) {
        $('#id_member').val(id);
        $('#kode_member').val(kode);
        $('#diskon').val('{{ $diskon }}');
        loadForm($('#diskon').val());
        $('#diterima').val(0).focus().select();
        hideMember();
    }

    function hideMember() {
        $('#modal-member').modal('hide');
    }

    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload(() => loadForm($('#diskon').val()));
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                    return;
                });
        }
    }

    function loadForm(diskon = 0, diterima = 0) {
        $('#total').val($('.total').text());
        $('#total_item').val($('.total_item').text());

        $.get(`{{ url('/transaksi/loadform') }}/${diskon}/${$('.total').text()}/${diterima}`)
            .done(response => {
                $('#totalrp').val('Rp. '+ response.totalrp);
                $('#bayarrp').val('Rp. '+ response.bayarrp);
                $('#bayar').val(response.bayar);
                $('.tampil-bayar').text('Bayar: Rp. '+ response.bayarrp);
                $('.tampil-terbilang').text(response.terbilang);

                $('#kembali').val('Rp.'+ response.kembalirp);
                if ($('#diterima').val() != 0) {
                    $('.tampil-bayar').text('Kembali: Rp. '+ response.kembalirp);
                    $('.tampil-terbilang').text(response.kembali_terbilang);
                }
            })
            .fail(errors => {
                alert('Tidak dapat menampilkan data');
                return;
            })
    }

    document.addEventListener('DOMContentLoaded', function () {
    const inputKodeProduk = document.getElementById('kode_produk');
    const inputMetodePembayaran = document.getElementById('metode_pembayaran');
    let currentJumlahIndex = -1;
    
    if (inputKodeProduk) {
        inputKodeProduk.focus();

        inputKodeProduk.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                tampilProduk();

                // Tunggu modal terbuka, lalu fokus ke tombol pilih pertama
                setTimeout(() => {
                    const modal = document.getElementById('modal-produk');
                    if (modal) {
                        const tombolPilih = modal.querySelector('.btn-pilih-produk[tabindex="0"]');
                        if (tombolPilih) {
                            tombolPilih.focus();
                        }
                    }
                }, 500); // Bisa disesuaikan jika modal butuh waktu muncul
            }
        });
    }

    // Shortcut F2 untuk buka form Member
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F2') {
            e.preventDefault();
            tampilMember();
        }

        // CTRL + B untuk fokus ke kolom diterima
        if (e.ctrlKey && e.key.toLowerCase() === 'b') {
            e.preventDefault();
            const inputDiterima = document.getElementById('diterima');
            if (inputDiterima) {
                inputDiterima.focus();
                inputDiterima.select();
            }
        }

        // CTRL + S untuk simpan transaksi
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            document.querySelector('.form-penjualan').submit();
        }

        // ENTER di kolom diterima langsung hitung kembali dan fokus ke tombol simpan
        const diterima = document.getElementById('diterima');
        if (document.activeElement === diterima && e.key === 'Enter') {
            e.preventDefault();
            // Opsional: hitung kembali (jika fungsi ada)
            // hitungKembali();
            document.querySelector('.btn-simpan').focus();
        }

        // CTRL + B: fokus ke kolom diterima
        if (e.ctrlKey && e.key.toLowerCase() === 'b') {
            e.preventDefault();
            const inputDiterima = document.getElementById('diterima');
            if (inputDiterima) {
                inputDiterima.focus();
                inputDiterima.select();
            }
        }

        if (e.ctrlKey && e.key === 'm') {
                e.preventDefault();
                inputMetodePembayaran.focus();
            }

        if (e.ctrlKey && e.key === 'q') {
            e.preventDefault();

            setTimeout(() => {
                const allInputs = document.querySelectorAll('.input-jumlah');

                if (allInputs.length === 0) {
                    console.warn('Tidak ada input jumlah ditemukan.');
                    return;
                }

                currentJumlahIndex++;

                if (currentJumlahIndex >= allInputs.length) {
                    currentJumlahIndex = 0; // ulang dari awal jika sudah di akhir
                }

                const inputJumlah = allInputs[currentJumlahIndex];
                inputJumlah.focus();
                inputJumlah.select();

                // Hindari double listener
                if (!inputJumlah.dataset.listenerAdded) {
                    inputJumlah.addEventListener('keydown', function handler(ev) {
                        if (ev.key === 'Enter') {
                            ev.preventDefault();

                            const id = inputJumlah.dataset.id;
                            const jumlah = parseInt(inputJumlah.value);

                            if (jumlah < 1 || jumlah > 10000) {
                                alert('Jumlah harus antara 1 dan 10000');
                                return;
                            }

                            $.post(`{{ url('/transaksi') }}/${id}`, {
                                '_token': $('[name=csrf-token]').attr('content'),
                                '_method': 'put',
                                'jumlah': jumlah
                            })
                            .done(() => {
                                table.ajax.reload(() => loadForm($('#diskon').val()));
                            })
                            .fail(() => {
                                alert('Gagal menyimpan jumlah');
                            });
                        }
                    });

                    inputJumlah.dataset.listenerAdded = true;
                }
            }, 100);
        }
        if (e.key === 'F1') {
                e.preventDefault();
                tampilProduk();

                setTimeout(() => {
                    const modal = document.getElementById('modal-produk');
                    if (modal) {
                        const tombolPilih = modal.querySelector('.btn-pilih-produk[tabindex="0"]');
                        if (tombolPilih) {
                            tombolPilih.focus();
                        }
                    }
                }, 500);
            }        
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#toggleShortcut').click(function () {
            $('#shortcutContent').slideToggle();
            $('#iconToggle').toggleClass('fa-chevron-down fa-chevron-up');
        });
    });
</script>
@endpush
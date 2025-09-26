@extends('layouts.master')

@section('title')
    Transaksi Penjualan Kredit Pelanggan
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

    .table-pembelian tbody tr:last-child {
        display: none;
    }

    @media(max-width: 768px) {
        .tampil-bayar {
            font-size: 3em;
            height: 70px;
            padding-top: 5px;
        }
    }

    .swal2-title-large {
        font-size: 50px !important; /* Judul lebih besar */
    }

    .swal2-content-large {
        font-size: 100px !important; /* Isi teks lebih besar */
    }

     /* Memperbesar ukuran title bawaan swal2 */
     .swal2-title {
        font-size: 3rem !important; /* misal 48px */
    }

    /* Memperbesar isi konten bawaan swal2 */
    .swal2-html-container {
        font-size: 2.5rem !important; /* misal 40px */
    }

    /* (Optional) kalau ada input di swal2 */
    .swal2-input {
        font-size: 2rem !important;
    }

    .swal2-confirm-large {
    font-size: 1.5rem !important; /* Membesarkan tulisan button OK */
    padding: 1rem 2rem !important; /* Membesarkan ukuran button */
    }

    .swal2-cancel-large {
        font-size: 1.5rem !important; /* Membesarkan tulisan button Cancel */
        padding: 1rem 2rem !important; /* Membesarkan ukuran button */
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
    <li class="active">Transaksi Pembelian</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <table>
                    <tr>
                        <td>Pelanggan</td>
                        <td>: {{ $pelanggan->nama }}</td>
                    </tr>
                    <tr>
                        <td>Telepon</td>
                        <td>: {{ $pelanggan->telepon }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: {{ $pelanggan->alamat }}</td>
                    </tr>
                </table>
            </div>
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

                <table class="table table-striped table-bordered table-penjualankredit" style="background-color: #f0f0f0;">
                    <thead style="background-color: #d3d3d3;"> <!-- Latar belakang abu-abu pada header tabel -->
                        <tr>
                            <th width="5%" style="text-align: center;">No</th>
                            <th style="text-align: center;">Kode</th>
                            <th style="text-align: center;">Nama</th>
                            <th style="text-align: center;">Merk</th>
                            <th style="text-align: center;">Harga</th>
                            <th width="15%" style="text-align: center;">Jumlah</th>
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
                                    <li><span class="shortcut-key"><kbd>Ctrl</kbd> + <kbd>M</kbd></span>: <span class="shortcut-desc">Untuk fokus ke metode pembayaran</span></li>
                                    <li><span class="shortcut-key"><kbd>F1</kbd></span>: <span class="shortcut-desc">Untuk menampilkan daftar produk</span></li>
                                    <li><span class="shortcut-key"><kbd>Ctrl</kbd> + <kbd>B</kbd></span>: <span class="shortcut-desc">Untuk fokus ke kolom bayar</span></li>
                                    <li><span class="shortcut-key"><kbd>Ctrl</kbd> + <kbd>S</kbd></span>: <span class="shortcut-desc">Untuk simpan transaksi</span></li>                                   
                                    <li><span class="shortcut-key"><kbd>Tab</kbd></span>: <span class="shortcut-desc">Untuk pindah ke input berikutnya</span></li>
                                    <li><span class="shortcut-key"><kbd>Ctrl</kbd> + <kbd>J</kbd></span>: <span class="shortcut-desc">Untuk fokus ke kolom jatuh tempo</span></li>
                                    <li><span class="shortcut-key"><kbd>Ctrl</kbd> + <kbd>D</kbd></span>: <span class="shortcut-desc">Untuk fokus ke kolom diskon</span></li>
                                    <li><span class="shortcut-key"><kbd>Shift</kbd> + <kbd>Tab</kbd></span>: <span class="shortcut-desc">Untuk kembali ke input sebelumnya</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="alert {{ $status_class }}" style="{{ $status_style }} color: white; font-size: 18px;">
                            Status Pembayaran: <strong>{{ ucfirst($status_pembayaran) }}</strong>
                        </div>

                        <form action="{{ route('penjualankredit.store') }}" class="form-penjualankredit" method="post">
                            @csrf
                            <input type="hidden" name="id_penjualan" value="{{ $id_penjualan }}">
                            <input type="hidden" name="total" id="total">
                            <input type="hidden" name="total_item" id="total_item">
                            <input type="hidden" name="bayar" id="bayar">

                            <div class="form-group row">
                                <label for="totalrp" class="col-lg-2 control-label">Total</label>
                                <div class="col-lg-8">
                                    <input type="text" id="totalrp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="diskon" class="col-lg-2 control-label">Diskon</label>
                                <div class="col-lg-8">
                                    <input type="number" name="diskon" min="0" max="100" id="diskon" class="form-control" value="{{ $diskon }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="harga_diskon" class="col-lg-2 control-label">Harga Diskon</label>
                                <div class="col-lg-8">
                                    <input type="text" id="harga_diskon" class="form-control" value="{{ number_format($harga_diskon, 0, ',', '.') }}" readonly>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="metode_pembayaran" class="col-lg-2 control-label">Metode Pembayaran</label>
                                <div class="col-lg-8">
                                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                                        <option value="">-- Pilih Metode --</option>
                                        <option value="tunai">Tunai</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="metode" class="col-lg-2 control-label">Jenis Pembayaran</label>
                                <div class="col-lg-8">
                                    <input type="hidden" name="status_pembayaran" value="hutang">
                                    <input type="text" class="form-control" value="Hutang" readonly style="background-color: rgba(169, 169, 169, 0.4);">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="jatuh_tempo" class="col-lg-2 control-label">Jatuh Tempo</label>
                                <div class="col-lg-8">
                                    <input type="date" name="jatuh_tempo" id="jatuh_tempo" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bayar" class="col-lg-2 control-label">Bayar</label>
                                <div class="col-lg-8">
                                    <input type="text" id="bayarrp" class="form-control" style="background-color: rgba(169, 169, 169, 0.4);">
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="sisa_hutang" class="col-lg-2 control-label">Sisa Hutang</label>
                                <div class="col-lg-8">
                                    <input type="text" id="sisa_hutangrp" class="form-control" readonly>
                                    <input type="hidden" name="sisa_hutang" id="sisa_hutang">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <a href="{{ route('penjualankredit.index') }}" class="btn btn-secondary btn-sm btn-flat pull-right">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary btn-sm btn-flat pull-right btn-simpan"><i class="fa fa-floppy-o"></i> Simpan Transaksi</button>
            </div>

            
        </div>
    </div>
</div>

@includeIf('penjualan_detailkredit.produk')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- TODO: Remove ".sandbox" from script src URL for production environment. Also input your client key in "data-client-key" -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<SB-Mid-client-It33ujTu9rnxYkH7>"></script>
<script>
    let table, table2;

    $(function () {
        
        $('#status_pembayaran').on('change', function() {
            if ($(this).val() === 'hutang') {
                $('#jatuh_tempo_field').show();
            } else {
                $('#jatuh_tempo_field').hide();
            }
        });

        $('body').addClass('sidebar-collapse');

        table = $('.table-penjualankredit').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('penjualan_detailkredit.data', $id_penjualan) }}',
            },
            columns: [
                {data: 'DT_RowIndex', className: 'text-center'},
                {data: 'kode_produk', className: 'text-center'},
                {data: 'nama_produk', className: 'text-center'},
                {data: 'merk', className: 'text-center'},
                {data: 'harga_jual', className: 'text-center'},
                {data: 'jumlah', className: 'text-center'},
                {data: 'subtotal', className: 'text-center'},
                {data: 'aksi', searchable: false, sortable: false, className: 'text-center'},
            ],
            dom: 'Brt',
            bSort: false,
            paginate: false
        })
        .on('draw.dt', function () {
            loadForm($('#diskon').val());
        });
        table2 = $('.table-produk').DataTable();

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

            $.post(`{{ url('/penjualan_detailkredit') }}/${id}`, {
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
        });

        $(document).on('input', '#diskon', function () {
            if ($(this).val() == "") {
                $(this).val(0).select();
            }

            loadForm($(this).val());
        });

        $('.btn-simpan').on('click', function () {
        const metodePembayaran = $('#metode_pembayaran').val();
        const jatuhTempo = $('#jatuh_tempo').val();
        const bayar = parseFloat($('#jumlah_bayar').val()) || 0;
        const subtotal = parseFloat($('#subtotal').val()) || 0;
        const hargaDiskon = parseFloat($('#harga_diskon').val()) || 0;

        // Validasi jika metode pembayaran belum dipilih
        if (!metodePembayaran) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Silakan pilih Metode Pembayaran terlebih dahulu!',
                width: '700px',
                padding: '3em',
                customClass: {
                    title: 'swal2-title-large',
                    content: 'swal2-content-large',
                    confirmButton: 'swal2-confirm-large'
                }
            });
            $('#metode_pembayaran').focus();
            return;
        }

        // Validasi jatuh tempo
        if ((metodePembayaran === 'transfer' || metodePembayaran === 'tunai') && !jatuhTempo) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Kolom Jatuh Tempo harus diisi terlebih dahulu!',
                width: '700px',
                padding: '3em',
                customClass: {
                    title: 'swal2-title-large',
                    content: 'swal2-content-large',
                    confirmButton: 'swal2-confirm-large'
                }
            });
            $('#jatuh_tempo').focus();
            return;
        }

        // Tampilkan modal konfirmasi
        Swal.fire({
            title: 'Simpan Transaksi?',
            text: "Apakah Anda yakin ingin menyimpan transaksi ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Tidak',
            width: '700px',
            padding: '3em',
            customClass: {
                title: 'swal2-title-large',
                content: 'swal2-content-large',
                confirmButton: 'swal2-confirm-large',
                cancelButton: 'swal2-cancel-large'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $('.form-penjualankredit').submit();
            }
        });
    });

    });

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

    function tambahProduk() {
        $.post('{{ route('penjualan_detailkredit.store') }}', $('.form-produk').serialize())
            .done(response => {
                $('#kode_produk').focus();
                table.ajax.reload(() => loadForm($('#diskon').val()));
            })
            .fail(errors => {
                alert('Tidak dapat menyimpan data');
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
                    table.ajax.reload(() => loadForm($('#diskon').val()));
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                    return;
                });
        }
    }

    function convertToTerbilang(angka) {
        const satuan = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

        function terbilang(n) {
            n = Math.floor(n);
            if (n < 12) return satuan[n];
            else if (n < 20) return terbilang(n - 10) + " belas";
            else if (n < 100) return terbilang(Math.floor(n / 10)) + " puluh " + terbilang(n % 10);
            else if (n < 200) return "seratus " + terbilang(n - 100);
            else if (n < 1000) return terbilang(Math.floor(n / 100)) + " ratus " + terbilang(n % 100);
            else if (n < 2000) return "seribu " + terbilang(n - 1000);
            else if (n < 1000000) return terbilang(Math.floor(n / 1000)) + " ribu " + terbilang(n % 1000);
            else if (n < 1000000000) return terbilang(Math.floor(n / 1000000)) + " juta " + terbilang(n % 1000000);
            else return "Angka terlalu besar";
        }

        if (angka === 0) return "Nol rupiah";
        return terbilang(angka).trim() + " rupiah";
    }

    function loadForm(diskon = 0) {
        $('#total').val($('.total').text());
        $('#total_item').val($('.total_item').text());

        $.get(`{{ url('/penjualan_detailkredit/loadform') }}/${diskon}/${$('.total').text()}`)
            .done(response => {
                let status = $('#status_pembayaran').val();

                // Tampilkan atau sembunyikan harga diskon
                if (parseInt(diskon) === 0) {
                    $('#harga_diskon').closest('.form-group').hide();
                } else {
                    $('#harga_diskon').closest('.form-group').show();
                }

                $('#totalrp').val('Rp. ' + response.totalrp);
                $('#harga_diskon').val(formatUang(response.bayar)); // Set default harga_diskon

                // Inisialisasi nilai bayar ke 0 dan sisa_hutang dari harga diskon
                let bayar = 0;
                let sisaHutangAwal = response.bayar; // Sudah dihitung dengan diskon jika ada

                $('#bayarrp').val('');
                $('#bayar').val(bayar);
                $('#sisa_hutangrp').val('Rp. ' + sisaHutangAwal.toLocaleString('id-ID'));
                $('#sisa_hutang').val(sisaHutangAwal);

                // ✅ Ubah tampil-bayar dan tampil-terbilang jadi nilai bayar
                $('.tampil-bayar').text('Bayar: Rp. 0');
                $('.tampil-terbilang').text(convertToTerbilang(0));

                // Tampilkan/hidden elemen tergantung status
                if (status === 'hutang') {
                    $('#form-sisa-hutang').show();
                    $('#jatuh_tempo_field').show();
                } else {
                    $('#form-sisa-hutang').show(); // Tetap tampilkan untuk tunai/transfer
                    $('#jatuh_tempo_field').hide();
                }

                // Tambahkan event input ke #bayarrp untuk hitung sisa hutang secara langsung
                $('#bayarrp').off('input').on('input', function () {
                    let bayarInput = toAngka($(this).val());
                    let total = toAngka($('#total').val());
                    let hargaDiskon = toAngka($('#harga_diskon').val());

                    // Gunakan nilai maksimum antara total dan hargaDiskon sebagai dasar pembayaran
                    let dasarPembayaran = Math.max(total, hargaDiskon);

                    // Hitung sisa hutang
                    let sisaHutang = dasarPembayaran - bayarInput;
                    if (sisaHutang < 0) sisaHutang = 0;

                    // Set value ke form tersembunyi
                    $('#bayar').val(bayarInput);
                    $('#sisa_hutang').val(sisaHutang);
                    $('#sisa_hutangrp').val('Rp. ' + sisaHutang.toLocaleString('id-ID'));

                    // ✅ Ganti ke nilai bayarInput, bukan sisaHutang lagi
                    $('.tampil-bayar').text('Bayar: Rp. ' + bayarInput.toLocaleString('id-ID'));
                    $('.tampil-terbilang').text(convertToTerbilang(bayarInput));

                    // Format kembali input uang
                    let formatBayar = bayarInput.toLocaleString('id-ID');
                    $(this).val(formatBayar);

                    // Tampilkan atau sembunyikan label
                    if (bayarInput > 0) {
                        $('.tampil-bayar').show();
                        $('.tampil-terbilang').show();
                    } else {
                        $('.tampil-bayar').hide();
                        $('.tampil-terbilang').hide();
                    }
                });
            })
            .fail(errors => {
                alert('Tidak dapat menampilkan data.');
            });
    }

    function toggleBayarInput() {
        let status = $('#status_pembayaran').val();

        if (status === 'tunai') {
            $('#bayarrp').prop('disabled', true).val('');
        } else {
            $('#bayarrp').prop('disabled', false);
        }
    }

    // Panggil fungsi saat halaman pertama kali dimuat
    toggleBayarInput();

    // Panggil fungsi saat dropdown berubah
    $('#status_pembayaran').on('change', function () {
        toggleBayarInput();
    });

    // Function to calculate and update harga_diskon
    function updateHargaDiskon() {
        const total = parseFloat($('#total').val().replace(/[^\d.-]/g, '')); // Get total as a number
        const diskon = parseFloat($('#diskon').val()); // Get discount percentage

        if (isNaN(diskon) || diskon <= 0) {
            // If no discount or invalid discount, just set harga_diskon to total
            $('#harga_diskon').val(total);
            $('#totalrp').val('Rp. ' + total.toLocaleString('id-ID'));
        } else {
            // Calculate discount amount
            const discountAmount = (diskon / 100) * total;
            const discountedTotal = total - discountAmount;

            // Update harga_diskon and total after discount
            $('#harga_diskon').val(formatUang(discountedTotal));
            $('#totalrp').val(formatUang(discountedTotal));
        }
    }

    $('#status_pembayaran').on('change', function() {
        loadForm(); // reload form untuk update nilai bayar sesuai metode
    });

    // Display or hide sisa hutang input based on payment method
    $('#status_pembayaran').on('change', function () {
        const metode = $(this).val();
        if (metode === 'hutang') {
            $('#jatuh_tempo_field').show();
            $('#form-sisa-hutang').show();

            // Calculate and set sisa hutang as the discounted total (harga_diskon)
            const sisaHutang = parseFloat($('#harga_diskon').val());
            $('#sisa_hutang').val(sisaHutang);
            $('#sisa_hutangrp').val('Rp. ' + sisaHutang.toLocaleString('id-ID'));

            // Set bayar to 0 when hutang
            $('#bayarrp').val('Rp. 0');
            $('#bayar').val(0);
        } else {
            $('#jatuh_tempo_field').hide();
            $('#form-sisa-hutang').hide();

            // Set sisa hutang to 0 if not hutang
            $('#sisa_hutang').val(0);
            $('#sisa_hutangrp').val('');
        }
    });

    // Format input #bayarrp ke dalam format uang saat diketik
    $('#bayarrp').on('input', function () {
        let input = $(this).val();
        let angkaBayar = toAngka(input); // Konversi ke angka

        // Format ke format uang (misal: 1000 => 1.000)
        let formatBayar = angkaBayar.toLocaleString('id-ID');
        $(this).val(formatBayar); // Set kembali hasil yang sudah diformat

        $('#bayar').val(angkaBayar); // Hidden field tetap angka untuk backend

        // Hitung sisa hutang
        let hargaDiskon = toAngka($('#harga_diskon').val()) || 0;
        let sisa = hargaDiskon - angkaBayar;

        // Jika bayar lebih dari harga diskon, sisa hutang = 0
        if (sisa < 0) sisa = 0;

        $('#sisa_hutang').val(sisa);
        $('#sisa_hutangrp').val('Rp. ' + sisa.toLocaleString('id-ID'));

        $('.tampil-bayar').text('Rp. ' + angkaBayar.toLocaleString('id-ID'));
        $('.tampil-terbilang').text(convertToTerbilang(angkaBayar));

        if (angkaBayar > 0) {
            $('.tampil-bayar').show();
            $('.tampil-terbilang').show();
        } else {
            $('.tampil-bayar').hide();
            $('.tampil-terbilang').hide();
        }
    });

    // Whenever diskon is updated (input)
    $('#diskon').on('input', function () {
        let diskon = parseFloat($(this).val());

        // Cek jika bukan angka atau diskon di luar batas
        if (isNaN(diskon) || diskon < 0) {
            alert('Diskon tidak boleh kurang dari 0%');
            $(this).val(0).focus();
        } else if (diskon > 100) {
            alert('Diskon tidak boleh lebih dari 100%');
            $(this).val(100).focus();
        }

        updateHargaDiskon(); // Recalculate harga_diskon when discount input changes
        loadForm($(this).val());
    });

    // Fungsi bantu untuk menghapus format rupiah ke angka
    function toAngka(rupiah) {
        return parseInt(rupiah.replace(/\./g, '').replace(/[^0-9]/g, '')) || 0;
    }

    // Fungsi bantu untuk format angka ke rupiah
    function formatUang(angka) {
        return 'Rp. ' + angka.toLocaleString('id-ID');
    }

    function angka(rp) {
    return parseInt(rp.replace(/[^0-9]/g, '')) || 0;
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kodeProdukInput = document.getElementById('kode_produk');
        const btnSimpan = document.querySelector('.btn-simpan');
        const inputBayar = document.getElementById('bayarrp');
        const inputDiskon = document.getElementById('diskon');
        const inputJatuhTempo = document.getElementById('jatuh_tempo');
        const inputMetodePembayaran = document.getElementById('metode_pembayaran');
        const inputKodeProduk = document.getElementById('kode_produk');
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

        // Shortcut keyboard
        document.addEventListener('keydown', function (e) {
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                btnSimpan.click();
            }
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                inputBayar.focus();
            }
            if (e.ctrlKey && e.key === 'd') {
                e.preventDefault();
                inputDiskon.focus();
            }
            if (e.ctrlKey && e.key === 'j') {
                e.preventDefault();

                const inputJatuhTempo = document.getElementById('jatuh_tempo');
                if (inputJatuhTempo) {
                    inputJatuhTempo.focus();

                    // Coba trigger date picker jika didukung
                    inputJatuhTempo.showPicker && inputJatuhTempo.showPicker(); 
                }
            }
            if (e.ctrlKey && e.key === 'm') {
                e.preventDefault();
                inputMetodePembayaran.focus();
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

                                    $.post(`{{ url('/penjualan_detailkredit') }}/${id}`, {
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
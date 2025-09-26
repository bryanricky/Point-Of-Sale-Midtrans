<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="" method="post" data-toggle="validator" class="form-horizontal" enctype="multipart/form-data">
            @csrf
            @method('post')

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="nama_produk" class="col-lg-2 col-lg-offset-1 control-label">Nama</label>
                        <div class="col-lg-6">
                            <input type="text" name="nama_produk" id="nama_produk" class="form-control" required autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="id_kategori" class="col-lg-2 col-lg-offset-1 control-label">Kategori</label>
                        <div class="col-lg-6">
                            <select name="id_kategori" id="id_kategori" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                @foreach ($kategori as $key => $item)
                                <option value="{{ $key }}">{{ $item }}</option>
                                @endforeach
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="merk" class="col-lg-2 col-lg-offset-1 control-label">Merk</label>
                        <div class="col-lg-6">
                            <input type="text" name="merk" id="merk" class="form-control">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="harga_beli" class="col-lg-2 col-lg-offset-1 control-label">Harga Beli</label>
                        <div class="col-lg-6">
                            <input type="number" name="harga_beli" id="harga_beli" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="harga_jual" class="col-lg-2 col-lg-offset-1 control-label">Harga Jual</label>
                        <div class="col-lg-6">
                            <input type="number" name="harga_jual" id="harga_jual" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="diskon" class="col-lg-2 col-lg-offset-1 control-label">Diskon</label>
                        <div class="col-lg-6">
                            <input type="number" name="diskon" id="diskon" class="form-control" value="0">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="stok" class="col-lg-2 col-lg-offset-1 control-label">Stok</label>
                        <div class="col-lg-6">
                            <input type="number" name="stok" id="stok" class="form-control" required value="0" min="0" step="1" oninput="validateStok(this)">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="path_foto" class="col-lg-2 col-lg-offset-1 control-label">Foto Produk</label>
                        <div class="col-lg-6 position-relative">
                            <input type="file" class="form-control" name="path_foto" onchange="previewFoto(this)">
                            
                            <div id="foto-preview-wrapper" style="position: relative; display: inline-block; margin-top: 10px;">
                                <img id="preview-foto" src="" class="img-thumbnail" width="100" style="display:none;">
                                <button type="button" id="btn-hapus-foto" 
                                        style="position: absolute; top: 2px; right: 2px; 
                                            background: rgba(255, 255, 255, 0.7); 
                                            border: none; border-radius: 50%; 
                                            width: 20px; height: 20px; 
                                            font-weight: bold; cursor: pointer; display:none;"
                                        title="Hapus Foto">&times;</button>
                            </div>
                            <!-- Input hidden flag hapus foto -->
                            <input type="hidden" name="hapus_foto" id="hapus_foto" value="0">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-flat btn-primary"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-sm btn-flat btn-warning" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Batal</button>
                </div>
            </div>
        </form>
        <div class="modal fade" id="confirmDeleteFotoModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteFotoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteFotoModalLabel">Konfirmasi Hapus Foto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    Apakah Anda yakin ingin menghapus foto produk ini?
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmDeleteFotoBtn" class="btn btn-danger btn-sm">Hapus</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function validateStok(input) {
        // Pastikan nilai stok tidak kurang dari 0
        if (parseInt(input.value) < 0) {
            input.value = 0;
        }
    }
</script>
@endpush
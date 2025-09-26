<div class="modal fade" id="modal-produk" tabindex="-1" role="dialog" aria-labelledby="modal-produk">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Pilih Produk</h4>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered table-produk">
                    <thead>
                        <th width="5%" class="text-center">No</th>
                        <th class="text-center">Kode</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center">Merk</th>
                        <th width="5%" class="text-center">Stok</th>
                        <th class="text-center">Harga Jual</th>
                        <th class="text-center"><i class="fa fa-cog"></i></th>
                    </thead>
                    <tbody>
                        @foreach ($produk as $key => $item)
                            <tr>
                                <td width="5%" class="text-center">{{ $key+1 }}</td>
                                <td class="text-center"><span class="label label-success">{{ $item->kode_produk }}</span></td>
                                <td>{{ $item->nama_produk }}</td>
                                <td class="text-center">{{ $item->merk }}</td>
                                <td width="5%" class="text-center">{{ $item->stok }}</td>
                                <td class="text-center">{{ $item->harga_jual }}</td>
                                <td class="text-center">
                                    <!-- Tombol Pilih dengan logika untuk stok kosong -->
                                    <a href="#"
                                        class="btn btn-xs btn-flat btn-pilih-produk 
                                                {{ $item->stok == 0 ? 'btn-danger disabled' : 'btn-primary' }}"
                                        onclick="{{ $item->stok > 0 ? "pilihProduk('$item->id_produk', '$item->kode_produk')" : 'return false' }}"
                                        tabindex="{{ $item->stok > 0 ? '0' : '-1' }}">
                                            <i class="fa fa-check-circle"></i> Pilih
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@push('scripts')
@endpush
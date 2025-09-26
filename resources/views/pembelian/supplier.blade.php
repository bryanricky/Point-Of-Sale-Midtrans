<div class="modal fade" id="modal-supplier" tabindex="-1" role="dialog" aria-labelledby="modal-supplier">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Pilih Supplier</h4>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered table-supplier">
                    <thead>
                        <th width="5%" class="text-center">No</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center">Telepon</th>
                        <th class="text-center">Alamat</th>
                        <th class="text-center"><i class="fa fa-cog"></i></th>
                    </thead>
                    <tbody>
                        @foreach ($supplier as $key => $item)
                            <tr>
                                <td width="5%" class="text-center">{{ $key+1 }}</td>
                                <td class="text-center">{{ $item->nama }}</td>
                                <td class="text-center">{{ $item->telepon }}</td>
                                <td>{{ $item->alamat }}</td>
                                <td class="text-center">
                                    <a href="{{ route('pembelian.create', $item->id_supplier) }}" class="btn btn-primary btn-xs btn-flat">
                                        <i class="fa fa-check-circle"></i>
                                        Pilih
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
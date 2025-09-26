<?php

use App\Http\Controllers\{    
    DashboardController,
    KategoriController,
    ProdukController,
    MemberController,
    SupplierController,
    PengeluaranController,
    PembelianController,
    PembelianDetailController,
    HutangController,
    PenjualanController,
    PenjualanDetailController,
    LaporanController,
    MidtransController,
    UserController,
    SettingController,
    PelangganController,
    PenjualanKreditController,
    PenjualanDetailKreditController,
    MidtransPelangganController,
    HutangPelangganController
};
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ResetPasswordController;
use Illuminate\Http\Request;

Route::get('/produk/cek-stok-menipis', function() {
    return \App\Models\Produk::where('stok', '<=', 5)
        ->get(['nama_produk', 'stok']);
});




Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/check-kasir-login', function (Request $request) {
    $user = \App\Models\User::where('email', $request->email)->first();
    $sudahLogin = false;

    if ($user && $user->level == 2) {
        $sudahLogin = \App\Models\LogKasir::where('user_id', $user->id)
            ->whereDate('login_at', now()->toDateString())
            ->exists();
    }

    return response()->json([
        'sudah_login' => $sudahLogin
    ]);
});


// ✅ Route reset password TANPA middleware auth
Route::post('/password/update', [ResetPasswordController::class, 'update'])->name('password.update');

Route::middleware(['auth:sanctum',config('jetstream.auth_session'),'verified',])->group(function () {
    Route::get('/dashboard', function () {return view('home'); })->name('dashboard');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/kategori/data', [KategoriController::class, 'data'])->name('kategori.data');
    Route::resource('/kategori', KategoriController::class);

    Route::get('/produk/data', [ProdukController::class, 'data'])->name('produk.data');
    Route::post('/produk/delete-selected', [ProdukController::class, 'deleteSelected'])->name('produk.delete_selected');
    Route::post('/produk/cetak-barcode', [ProdukController::class, 'cetakBarcode'])->name('produk.cetak_barcode');
    Route::resource('/produk', ProdukController::class);

    Route::get('/member/data', [MemberController::class, 'data'])->name('member.data');
    Route::resource('/member', MemberController::class);
    Route::post('/member/cetak-member', [MemberController::class, 'cetakMember'])->name('member.cetak_member');

    Route::get('/supplier/data', [SupplierController::class, 'data'])->name('supplier.data');
    Route::resource('/supplier', SupplierController::class);

    Route::get('/pelanggan/data', [PelangganController::class, 'data'])->name('pelanggan.data');
    Route::resource('/pelanggan', PelangganController::class);

    Route::get('/pengeluaran/data', [PengeluaranController::class, 'data'])->name('pengeluaran.data');
    Route::resource('/pengeluaran', PengeluaranController::class);

    Route::get('/pembelian/pembayaran/selesai', [PembelianController::class, 'pembayaranSuccess'])->name('pembelian.selesai');
    Route::get('/pembelian/midtrans', [PembelianController::class, 'formMidtrans'])->name('pembelian.midtrans');
    Route::get('/pembelian/data', [PembelianController::class, 'data'])->name('pembelian.data');
    Route::get('/pembelian/{id}/create', [PembelianController::class, 'create'])->name('pembelian.create');
    Route::resource('/pembelian', PembelianController::class)
        ->except('create');
    Route::get('/pembelian/pembayaran/tunai/{id}', [PembelianController::class, 'pembayaranTunai'])->name('pembelian.pembayaran_tunai');
    Route::get('/pembelian/cetak-struk-tunai/{id}', [PembelianController::class, 'cetakStrukTunai'])->name('pembelian.cetak_struk_tunai');

    Route::get('/pembelian_detail/{id}/data', [PembelianDetailController::class, 'data'])->name('pembelian_detail.data');
    Route::get('/pembelian_detail/loadform/{diskon}/{total}', [PembelianDetailController::class, 'loadForm'])->name('pembelian_detail.load_form');
    Route::resource('/pembelian_detail', PembelianDetailController::class)
        ->except('create', 'show', 'edit');

    Route::get('/penjualankredit/data', [PenjualanKreditController::class, 'data'])->name('penjualankredit.data');
    Route::get('/penjualankredit/{id}/create', [PenjualanKreditController::class, 'create'])->name('penjualankredit.create');
    Route::get('/penjualankredit/pembayaran/tunai/{id}', [PenjualanKreditController::class, 'pembayaranTunai'])->name('penjualankredit.pembayaran_tunai');
    Route::resource('/penjualankredit', PenjualanKreditController::class)
        ->except('create');

    Route::get('/penjualankredit/cetak-struk-tunai/{id}', [PenjualanKreditController::class, 'cetakStrukTunai'])->name('penjualankredit.cetak_struk_tunai');

    Route::get('/midtranspelanggan/payment/{snap_token}', [MidtransPelangganController::class, 'paymentForm'])->name('midtranspelanggan.payment');
    Route::get('/midtranspelanggan/success', [MidtransPelangganController::class, 'pembayaranSuccess'])->name('midtranspelanggan.success');

    Route::get('/penjualan_detailkredit/{id}/data', [PenjualanDetailKreditController::class, 'data'])->name('penjualan_detailkredit.data');
    Route::get('/penjualan_detailkredit/loadform/{diskon}/{total}', [PenjualanDetailKreditController::class, 'loadForm'])->name('penjualan_detailkredit.load_form');
    Route::resource('/penjualan_detailkredit', PenjualanDetailKreditController::class)
        ->except('create', 'show', 'edit');

    Route::post('/create-midtrans-snap-token', [MidtransController::class, 'createSnapToken'])->name('createMidtransSnapToken');
    Route::get('/pembelian_detail/success', [MidtransController::class, 'success'])->name('success');

    Route::prefix('hutang')->group(function () {
        Route::get('/', [HutangController::class, 'index'])->name('hutang.index');
        Route::get('/hutang/{id}/bayar', [HutangController::class, 'showBayarForm'])->name('hutang.bayar.form');
        Route::post('/hutang/{id}/bayar', [HutangController::class, 'prosesBayar'])->name('hutang.bayar.proses');
        
        Route::get('/hutang/bayar/{id}', [HutangController::class, 'formBayar'])->name('hutang.bayar.form');
        Route::post('/hutang/bayar/{id}', [HutangController::class, 'simpanBayar'])->name('hutang.bayar.simpan');

        Route::delete('/hutang/bayar/hapus/{id}', [HutangController::class, 'hapusBayar'])->name('hutang.bayar.hapus');
        Route::get('/hutang/pembayaran', [HutangController::class, 'pembayaran'])->name('hutang.pembayaran');

        Route::get('/hutang/form-lunas/{id}', [HutangController::class, 'formLunas'])->name('hutang.formLunas');
        Route::post('/hutang/bayar-lunas/{id}', [HutangController::class, 'bayarLunas'])->name('hutang.bayar.lunas');

        Route::get('/hutang/success/{id}', [HutangController::class, 'pembayaranSuccess'])->name('hutang.success');
        Route::get('/pembayaran-tunai/berhasil/{id}', [HutangController::class, 'pembayaranTunai'])->name('hutang.pembayarantunai');

        Route::get('/hutang/cetak-struk/{id}', [HutangController::class, 'pembayaranTunai'])
        ->name('hutang.cetak_struk_tunai');

        Route::get('/hutang/cetak-struk/{id}', [HutangController::class, 'cetakStrukTunai'])
        ->name('hutang.cetak_struk_tunai');
    });

    Route::get('hutang_pelanggan', [HutangPelangganController::class, 'index'])->name('hutang_pelanggan.index');
    Route::get('hutang_pelanggan/bayar/{id}', [HutangPelangganController::class, 'formBayar'])->name('hutang_pelanggan.bayar');

    Route::post('/hutang_pelanggan/informasi/bayar/{id}', [HutangPelangganController::class, 'simpanBayar'])->name('hutang_pelanggan.bayar.simpan');
    Route::get('/hutang_pelanggan/pembayaran_tunai/berhasil/{id}', [HutangPelangganController::class, 'pembayaranTunai'])->name('hutang_pelanggan.pembayarantunai');

    Route::get('/hutang_pelanggan/midtrans/form-lunas/{id}', [HutangPelangganController::class, 'formLunas'])->name('hutang_pelanggan.formLunas');
    Route::get('/hutang_pelanggan/midtrans/success/{id}', [HutangPelangganController::class, 'pembayaranSuccess'])->name('hutang_pelanggan.success');

    Route::get('/hutang_pelanggan/pembayaran_lunas/{id}', [HutangPelangganController::class, 'formBayar'])->name('hutang_pelanggan.bayar.form');
    Route::get('/hutang_pelanggan/pembayaran_lunas', [HutangPelangganController::class, 'pembayaranlunas'])->name('hutang_pelanggan.pembayaran_lunas');
    Route::delete('/hutang_pelanggan/bayar/hapus/{id}', [HutangPelangganController::class, 'hapusBayar'])->name('hutang_pelanggan.bayar.hapus');

    Route::get('/hutang-pelanggan/cetak-struk/{id}', [HutangPelangganController::class, 'pembayaranTunai'])
    ->name('hutang_pelanggan.cetak_struk_tunai');

    Route::get('/hutang-pelanggan/cetak-struk/{id}', [HutangPelangganController::class, 'cetakStrukTunai'])
    ->name('hutang_pelanggan.cetak_struk_tunai');

    Route::get('/penjualan/data', [PenjualanController::class, 'data'])->name('penjualan.data');
    Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
    Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])->name('penjualan.show');
    Route::delete('/penjualan/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');
    
    Route::get('/transaksi/baru', [PenjualanController::class, 'create'])->name('transaksi.baru');
    Route::post('/transaksi/simpan', [PenjualanController::class, 'store'])->name('transaksi.simpan');
    Route::get('/transaksi/selesai', [PenjualanController::class, 'selesai'])->name('transaksi.selesai');
    Route::get('/transaksi/nota-kecil', [PenjualanController::class, 'notaKecil'])->name('transaksi.nota_kecil');
    Route::get('/transaksi/nota-besar', [PenjualanController::class, 'notaBesar'])->name('transaksi.nota_besar');
    Route::get('/transaksi/pembayaran-midtrans/{snap_token}', [PenjualanController::class, 'pembayaranMidtrans'])->name('transaksi.pembayaranmidtrans');

    Route::get('/transaksi/{id}/data', [PenjualanDetailController::class, 'data'])->name('transaksi.data');
    Route::get('/transaksi/loadform/{diskon}/{total}/{diterima}', [PenjualanDetailController::class, 'loadForm'])->name('transaksi.load_form');
    Route::resource('/transaksi', PenjualanDetailController::class)
        ->except('create', 'show', 'edit');
    
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/data/{awal}/{akhir}', [LaporanController::class, 'data'])->name('laporan.data');
    Route::get('/laporan/export/pdf/{jenis}/{awal}/{akhir}', [LaporanController::class, 'exportPDF'])->name('laporan.export_pdf');

    Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
    Route::resource('/user', UserController::class);

    Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
    Route::get('/setting/first', [SettingController::class, 'show'])->name('setting.show');
    Route::post('/setting', [SettingController::class, 'update'])->name('setting.update');

    Route::get('/profil', [UserController::class, 'profil'])->name('user.profil');
    Route::post('/profil', [UserController::class, 'updateProfil'])->name('user.update_profil');

});
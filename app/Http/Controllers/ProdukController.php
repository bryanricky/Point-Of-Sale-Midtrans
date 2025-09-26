<?php

namespace App\Http\Controllers;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Events\ProdukStokMenipis;


class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $kategori = Kategori::all()->pluck('nama_kategori', 'id_kategori');

        // Cek produk dengan stok 0
        $produkKosong = Produk::where('stok', '<=', 0)->get(); // hasilnya Collection

        // Cek produk hampir habis (stok <= 5 dan > 0)
        $produkHampirHabis = Produk::where('stok', '>', 0)->where('stok', '<=', 5)->get();

        // 🔥 Loop dan broadcastkan event satu per satu
    foreach ($produkHampirHabis as $produk) {
        event(new ProdukStokMenipis($produk->nama_produk, $produk->stok));
    }

    // ✅ TAMBAHKAN INI agar stok = 0 juga di-broadcast
foreach ($produkKosong as $produk) {
    event(new ProdukStokMenipis($produk->nama_produk, $produk->stok));
}

        return view('produk.index', compact('kategori','produkKosong','produkHampirHabis'));
    }

    public function data()
    {
        $produk = Produk::leftJoin('kategori', 'kategori.id_kategori', 'produk.id_kategori')
            ->select('produk.*', 'nama_kategori')
            // ->orderBy('kode_produk', 'asc')
            ->get();

        return datatables()
            ->of($produk)
            ->addIndexColumn()
            ->addColumn('select_all', function ($produk) {
                return '
                    <input type="checkbox" name="id_produk[]" value="'. $produk->id_produk .'">
                ';
            })
            ->addColumn('kode_produk', function ($produk) {
                return '<span class="label label-success">'. $produk->kode_produk .'</span>';
            })
            ->addColumn('harga_beli', function ($produk) {
                return format_uang($produk->harga_beli);
            })
            ->addColumn('harga_jual', function ($produk) {
                return format_uang($produk->harga_jual);
            })
            ->addColumn('diskon', function ($produk) {
                return number_format($produk->diskon, 0) . '%';
            })
            ->addColumn('stok', function ($produk) {
                $stok = $produk->stok < 0 ? 0 : $produk->stok; // kalau stok kurang dari 0, pakai 0
                return format_uang($stok);
            })
            ->addColumn('path_foto', function ($produk) {
                if ($produk->path_foto && file_exists(public_path($produk->path_foto))) {
                    $fotoUrl = asset($produk->path_foto);
                    return '<img src="'. $fotoUrl .'" width="100px" class="img-thumbnail" style="cursor:pointer;" onclick="showImagePopup(\''. $fotoUrl .'\')">';
                }
                return '<span class="text-muted">Tidak ada gambar</span>';
            })
            
            ->addColumn('aksi', function ($produk) {
                return '
                <div class="btn-container">
                    <div class="btn-group">
                        <button type="button" onclick="editForm(`'. route('produk.update', $produk->id_produk) .'`)" class="btn btn-xs btn-info btn-flat">
                            <i class="fa fa-pencil"></i> Edit
                        </button>
                        <button type="button" onclick="deleteData(`'. route('produk.destroy', $produk->id_produk) .'`)" class="btn btn-xs btn-danger btn-flat">
                            <i class="fa fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
                ';
            })
            ->rawColumns(['aksi', 'kode_produk', 'select_all', 'path_foto'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validasi input stok, pastikan tidak kurang dari 0
        $request['stok'] = max(0, $request->stok); // set stok minimal 0

        $produk = Produk::latest('id_produk')->first();
        $id_terakhir = optional($produk)->id_produk ?? 0;
        $request['kode_produk'] = 'P' . tambah_nol_didepan((int) $id_terakhir + 1, 6);

        // Simpan dulu data kecuali gambar
        $produk = Produk::create($request->only([
            'nama_produk',
            'id_kategori',
            'merk',
            'harga_beli',
            'harga_jual',
            'diskon',
            'stok',
            'kode_produk'
        ]));


        // Simpan file foto jika ada
        if ($request->hasFile('path_foto')) {
            $filename = $request->file('path_foto')->getClientOriginalName();
            $path = $request->file('path_foto')->storeAs('produk', $filename, 'public');

            // Simpan path foto ke database
            $produk->path_foto = 'storage/' . $path; // untuk diakses publik
            $produk->save();
        }

        // Redirect ke halaman daftar produk
        return redirect()->route('produk.index')->with('success', 'Data berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $produk = Produk::find($id);

        return response()->json($produk);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request, $id)
{
    $produk = Produk::findOrFail($id);
    $request['stok'] = max(0, $request->stok);

    // Hapus foto jika diminta
    if ($request->input('hapus_foto') === '1' && $produk->path_foto) {
        if (file_exists(public_path($produk->path_foto))) {
            unlink(public_path($produk->path_foto));
        }
        $produk->path_foto = null;
        $produk->save();
    }

    // Update hanya field yang ada di DB
    $produk->update($request->only([
        'nama_produk', 'id_kategori', 'merk', 'harga_beli',
        'harga_jual', 'diskon', 'stok'
    ]));

    // Upload foto baru jika ada
    if ($request->hasFile('path_foto')) {
        if ($produk->path_foto && file_exists(public_path($produk->path_foto))) {
            unlink(public_path($produk->path_foto));
        }

        $filename = $request->file('path_foto')->getClientOriginalName();
        $path = $request->file('path_foto')->storeAs('produk', $filename, 'public');

        $produk->path_foto = 'storage/' . $path;
        $produk->save();
    }

    // ===> Tambahkan ini: kalau stok <= 5, broadcast event
    if ($produk->stok <= 5) {
        event(new ProdukStokMenipis($produk->nama_produk, $produk->stok));
    }

    return redirect()->route('produk.index')->with('success', 'Data berhasil disimpan.');
}



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $produk = Produk::find($id);
        $produk->delete();

        return response(null, 204);
    }

    public function deleteSelected(Request $request)
    {
        foreach ($request->id_produk as $id) {
            $produk = Produk::find($id);
            $produk->delete();
        }

        return response(null, 204);
    }

    public function cetakBarcode(Request $request)
    {
        $dataproduk = array();
        foreach ($request->id_produk as $id) {
            $produk = Produk::find($id);
            $dataproduk[] = $produk;
        }

        $no  = 1;
        $pdf = PDF::loadView('produk.barcode', compact('dataproduk', 'no'));
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream('produk.pdf');
    }
}

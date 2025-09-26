<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;

class PelangganController extends Controller
{
    public function index()
    {
        return view('pelanggan.index');
    }

    public function data()
    {
        $pelanggan = Pelanggan::orderBy('id_pelanggan', 'desc')->get();

        return datatables()
            ->of($pelanggan)
            ->addIndexColumn()
            ->addColumn('path_foto', function ($row) {
                $url = asset($row->path_foto);
                return '<img src="' . $url . '" width="100px" class="img-thumbnail hover-scale" style="cursor: pointer;" onclick="showImageModal(\'' . $url . '\')">';
            })
            ->addColumn('aksi', function ($pelanggan) {
                return '
                <div class="btn-container">
                    <div class="btn-group">
                        <button type="button" onclick="editForm(`'. route('pelanggan.update', $pelanggan->id_pelanggan) .'`)" class="btn btn-xs btn-info btn-flat">
                            <i class="fa fa-pencil"></i> Edit
                        </button>
                        <button type="button" onclick="deleteData(`'. route('pelanggan.destroy', $pelanggan->id_pelanggan) .'`)" class="btn btn-xs btn-danger btn-flat">
                            <i class="fa fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
                ';
            })
            ->rawColumns(['aksi','path_foto'])
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
        $data = $request->except('path_foto');
        $pelanggan = Pelanggan::create($data);

        // Simpan file foto jika ada
        if ($request->hasFile('path_foto')) {
            $filename = time() . '_' . $request->file('path_foto')->getClientOriginalName();
            $path = $request->file('path_foto')->storeAs('pelanggan', $filename, 'public');

            $pelanggan->path_foto = 'storage/' . $path;
            $pelanggan->save();
        }

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pelanggan = Pelanggan::find($id);

        return response()->json($pelanggan);
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
    $pelanggan = Pelanggan::findOrFail($id);

    // Update data tanpa path_foto
    $pelanggan->update($request->except('path_foto', 'hapus_foto'));

    // Jika checkbox hapus foto dicentang
    if ($request->filled('hapus_foto') && $request->hapus_foto == 1) {
        if ($pelanggan->path_foto && file_exists(public_path($pelanggan->path_foto))) {
            unlink(public_path($pelanggan->path_foto));
        }

        $pelanggan->path_foto = null;
        $pelanggan->save();
    }

    // Jika ada file foto baru yang diupload
    if ($request->hasFile('path_foto')) {
        // Hapus foto lama jika ada
        if ($pelanggan->path_foto && file_exists(public_path($pelanggan->path_foto))) {
            unlink(public_path($pelanggan->path_foto));
        }

        // Simpan foto baru
        $filename = time() . '_' . $request->file('path_foto')->getClientOriginalName();
        $path = $request->file('path_foto')->storeAs('pelanggan', $filename, 'public');

        $pelanggan->path_foto = 'storage/' . $path;
        $pelanggan->save();
    }

    return response()->json('Data berhasil diperbarui', 200);
}


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        // Hapus foto jika ada
        if ($pelanggan->path_foto && file_exists(public_path($pelanggan->path_foto))) {
            unlink(public_path($pelanggan->path_foto));
        }

        $pelanggan->delete();
        return response(null, 204);
    }
}

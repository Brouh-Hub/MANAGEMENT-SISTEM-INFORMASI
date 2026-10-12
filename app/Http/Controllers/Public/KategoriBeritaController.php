<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\KategoriBerita;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KategoriBeritaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = KategoriBerita::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function ($row) {
                    $detail = htmlspecialchars(json_encode($row));

                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="javascript:void(0)" class="btn-update btn btn-sm btn-primary" data-detail="' . $detail . '" data-id="' . $row->id . '">Ubah</a>';
                    $btn .= '<a href="javascript:void(0)" class="btn-delete btn btn-sm btn-danger" data-id="' . $row->id . '">Hapus</a>';
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.kategori-berita.index', [
            'title'    => 'Kategori Berita',
            'subtitle' => 'List Kategori Berita',
        ]);
    }

    public function create() {}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255|unique:kategori_berita,nama_kategori',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ooopps',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            KategoriBerita::create($validator->validated());

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil simpan kategori berita',
                'data'    => null,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal simpan kategori berita, ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    public function show(string $id) {}
    public function edit(string $id) {}

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255|unique:kategori_berita,nama_kategori,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ooopps',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $kategori = KategoriBerita::findOrFail($id);
            $kategori->update($validator->validated());

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil ubah kategori berita',
                'data'    => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kategori berita tidak ditemukan',
                'data'    => null,
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal ubah kategori berita, ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $kategori = KategoriBerita::findOrFail($id);
            $kategori->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil hapus kategori berita',
                'data'    => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kategori berita tidak ditemukan',
                'data'    => null,
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal hapus kategori berita, ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }
}

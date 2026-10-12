<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BeritaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Berita::with('kategori')->latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('kategori', fn($row) => $row->kategori->nama_kategori ?? '-')
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

        // Halaman publik: list berita yang published aja
        $berita = Berita::with('kategori')
            ->where('status', 'publish')
            ->latest()
            ->paginate(9);

        return view('public.berita.index', [
            'title'  => 'Berita Sekolah',
            'berita' => $berita,
        ]);
    }

    public function create() {}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul'              => 'required|string|max:255',
            'kategori_berita_id' => 'required|exists:kategori_berita,id',
            'konten'             => 'required|string',
            'thumbnail'          => 'nullable|image|max:2048',
            'status'             => 'required|in:draft,publish',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ooopps',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['slug'] = Str::slug($data['judul']) . '-' . uniqid();
            $data['user_id'] = auth()->id();

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $request->file('thumbnail')->store('berita-thumbnail', 'public');
            }

            Berita::create($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil simpan berita',
                'data'    => null,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal simpan berita, ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    public function show(string $slug)
    {
        $berita = Berita::where('slug', $slug)->where('status', 'publish')->firstOrFail();

        return view('public.berita.show', [
            'title'  => $berita->judul,
            'berita' => $berita,
        ]);
    }

    public function edit(string $id) {}

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'judul'              => 'required|string|max:255',
            'kategori_berita_id' => 'required|exists:kategori_berita,id',
            'konten'             => 'required|string',
            'thumbnail'          => 'nullable|image|max:2048',
            'status'             => 'required|in:draft,publish',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ooopps',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $berita = Berita::findOrFail($id);
            $data = $validator->validated();

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $request->file('thumbnail')->store('berita-thumbnail', 'public');
            }

            $berita->update($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil ubah berita',
                'data'    => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Berita tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal ubah berita, ' . $e->getMessage(), 'data' => null], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $berita = Berita::findOrFail($id);
            $berita->delete();

            return response()->json(['status' => 'success', 'message' => 'Berhasil hapus berita', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Berita tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal hapus berita, ' . $e->getMessage(), 'data' => null], 500);
        }
    }
}

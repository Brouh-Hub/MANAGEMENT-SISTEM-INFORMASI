<?php

namespace App\Http\Controllers;

use App\Models\TemplateSertifikat;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TemplateSertifikatController extends Controller
{
    /**
     * Field default yang bisa ditempel di atas gambar template.
     * Kalau butuh field baru (misal "kode_qr"), tinggal tambah di sini.
     */
    protected array $defaultFields = [
        ['field' => 'nomor_sertifikat', 'label' => 'Nomor Sertifikat', 'x' => 50,  'y' => 50,  'font_size' => 16, 'color' => '#000000', 'align' => 'left'],
        ['field' => 'judul',            'label' => 'Judul Sertifikat', 'x' => 400, 'y' => 150, 'font_size' => 28, 'color' => '#000000', 'align' => 'center'],
        ['field' => 'nama_penerima',    'label' => 'Nama Penerima',    'x' => 400, 'y' => 250, 'font_size' => 32, 'color' => '#1625C4', 'align' => 'center'],
        ['field' => 'deskripsi',        'label' => 'Deskripsi',        'x' => 400, 'y' => 320, 'font_size' => 14, 'color' => '#333333', 'align' => 'center'],
        ['field' => 'tanggal_terbit',   'label' => 'Tanggal Terbit',   'x' => 400, 'y' => 450, 'font_size' => 14, 'color' => '#000000', 'align' => 'center'],
    ];

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = TemplateSertifikat::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('preview', function ($row) {
                    return $row->file_path
                        ? '<img src="/storage/' . $row->file_path . '" style="width:70px;height:45px;object-fit:cover;border-radius:6px;">'
                        : '-';
                })
                ->addColumn('jumlah_field', function ($row) {
                    $struktur = json_decode($row->struktur_template, true) ?? [];
                    return count($struktur) . ' field';
                })
                ->addColumn('actions', function ($row) {
                    $detail = htmlspecialchars(json_encode($row));

                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('template-sertifikat.posisi', $row->id) . '" class="btn btn-sm btn-info text-white me-1">Atur Posisi</a>';
                    $btn .= '<a href="javascript:void(0)" class="btn-update btn btn-sm btn-primary" data-detail="' . $detail . '" data-id="' . $row->id . '">Ubah</a>';
                    $btn .= '<a href="javascript:void(0)" class="btn-delete btn btn-sm btn-danger" data-id="' . $row->id . '">Hapus</a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['preview', 'actions'])
                ->make(true);
        }

        return view('admin.template-sertifikat.index', [
            'title'    => 'Template Sertifikat',
            'subtitle' => 'Kelola desain latar sertifikat',
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_template' => 'required|string|max:255',
            'kategori'      => 'nullable|string|max:100',
            'file'          => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $path = $request->file('file')->store('template-sertifikat', 'public');

            TemplateSertifikat::create([
                'nama_template'     => $request->nama_template,
                'kategori'          => $request->kategori,
                'file_path'         => $path,
                'struktur_template' => json_encode($this->defaultFields),
                'created_by'        => auth()->id(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Berhasil tambah template', 'data' => null], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal tambah template, ' . $e->getMessage(), 'data' => null], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_template' => 'required|string|max:255',
            'kategori'      => 'nullable|string|max:100',
            'file'          => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $template = TemplateSertifikat::findOrFail($id);

            $data = [
                'nama_template' => $request->nama_template,
                'kategori'      => $request->kategori,
            ];

            if ($request->hasFile('file')) {
                if ($template->file_path) {
                    Storage::disk('public')->delete($template->file_path);
                }
                $data['file_path'] = $request->file('file')->store('template-sertifikat', 'public');
            }

            $template->update($data);

            return response()->json(['status' => 'success', 'message' => 'Berhasil ubah template', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Template tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal ubah template, ' . $e->getMessage(), 'data' => null], 500);
        }
    }

    /**
     * Ganti hanya gambar latar template (dipakai dari halaman editor posisi),
     * tanpa perlu ngirim ulang nama_template/kategori.
     */
    public function updateGambar(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $template = TemplateSertifikat::findOrFail($id);

            if ($template->file_path) {
                Storage::disk('public')->delete($template->file_path);
            }

            $path = $request->file('file')->store('template-sertifikat', 'public');
            $template->update(['file_path' => $path]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil ganti gambar template',
                'data'    => ['file_path' => $path],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Template tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal ganti gambar, ' . $e->getMessage(), 'data' => null], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $template = TemplateSertifikat::findOrFail($id);

            if ($template->file_path) {
                Storage::disk('public')->delete($template->file_path);
            }
            $template->delete();

            return response()->json(['status' => 'success', 'message' => 'Berhasil hapus template', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Template tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal hapus template, ' . $e->getMessage(), 'data' => null], 500);
        }
    }

    /**
     * Halaman editor: atur posisi (x,y) tiap field di atas gambar template.
     */
    public function posisi(string $id)
    {
        $template = TemplateSertifikat::findOrFail($id);
        $struktur = json_decode($template->struktur_template, true);

        if (empty($struktur)) {
            $struktur = $this->defaultFields;
        }

        return view('admin.template-sertifikat.posisi', [
            'title'    => 'Atur Posisi Template',
            'subtitle' => $template->nama_template,
            'template' => $template,
            'struktur' => $struktur,
        ]);
    }

    public function updatePosisi(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'struktur_template'   => 'required|array|min:1',
            'struktur_template.*.field'     => 'required|string',
            'struktur_template.*.x'         => 'required|numeric',
            'struktur_template.*.y'         => 'required|numeric',
            'struktur_template.*.font_size' => 'required|numeric|min:6|max:100',
            'struktur_template.*.color'     => 'required|string',
            'struktur_template.*.align'     => 'required|in:left,center,right',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $template = TemplateSertifikat::findOrFail($id);
            $template->update([
                'struktur_template' => json_encode($request->struktur_template),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Berhasil simpan posisi template', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Template tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal simpan posisi, ' . $e->getMessage(), 'data' => null], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Models\TemplateSurat;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TemplateSuratController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = TemplateSurat::with('fields')
                ->latest()
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    if ($row->is_active) {
                        return '<span class="text-success">
                                    <i class="fa fa-circle" style="font-size:8px;"></i>
                                    Aktif
                                </span>';
                    }

                    return '<span class="text-secondary">
                                <i class="fa fa-circle" style="font-size:8px;"></i>
                                Nonaktif
                            </span>';
                })
                ->addColumn('actions', function ($row) {
                    $detail = htmlspecialchars(
                        json_encode($row),
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    $btn = '<div class="btn-group">';

                    $btn .= '<a href="javascript:void(0)"
                                class="btn-detail btn btn-sm btn-light"
                                data-detail="' . $detail . '"
                                title="Lihat">
                                <i class="fa fa-eye"></i>
                             </a>';

                    $fields = htmlspecialchars(
                        json_encode($row->fields),
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    $btn .= '<a href="javascript:void(0)"
                                class="btn-fields btn btn-sm btn-light text-primary"
                                data-id="' . $row->id . '"
                                data-name="' . htmlspecialchars($row->nama_template, ENT_QUOTES, 'UTF-8') . '"
                                data-fields="' . $fields . '"
                                title="Atur Input">
                                <i class="fa fa-gear"></i>
                             </a>';

                    $btn .= '<a href="javascript:void(0)"
                                class="btn-delete btn btn-sm btn-light text-danger"
                                data-id="' . $row->id . '"
                                title="Hapus">
                                <i class="fa fa-trash"></i>
                             </a>';

                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns([
                    'status_badge',
                    'actions',
                ])
                ->make(true);
        }

        return view('admin.template-surat.index', [
            'title' => 'Template Surat',
            'subtitle' => 'Kelola template yang digunakan untuk membuat surat keluar.',
        ]);
    }

    public function create()
    {
        return view('admin.template-surat.editor', [
            'title' => 'Buat Template Surat',
            'subtitle' => 'Atur format dan isi template surat.',
            'template' => null,
        ]);
    }

    public function edit(string $id)
    {
        $template = TemplateSurat::findOrFail($id);

        return view('admin.template-surat.editor', [
            'title' => 'Ubah Template Surat',
            'subtitle' => 'Ubah format dan isi template surat.',
            'template' => $template,
        ]);
    }

    /**
     * Ambil semua variabel {{nama_variabel}}
     * yang terdapat di dalam isi template.
     */
    private function extractVariables(string $content): array
    {
        preg_match_all(
            '/{{\s*([A-Za-z0-9_]+)\s*}}/',
            $content,
            $matches
        );

        return array_values(
            array_unique($matches[1] ?? [])
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_template' => 'required|string|max:255',
            'jenis_surat'   => 'nullable|string|max:255',
            'isi_template'  => 'required|string',
            'is_active'     => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ooopps',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            $data['variabel'] = $this->extractVariables(
                $data['isi_template']
            );

            $data['is_active'] = $request->boolean(
                'is_active',
                true
            );

            $data['user_id_pembuat'] = auth()->id();

            $template = TemplateSurat::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Template surat berhasil ditambahkan',
                'data' => $template,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan template surat, ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_template' => 'required|string|max:255',
            'jenis_surat'   => 'nullable|string|max:255',
            'isi_template'  => 'required|string',
            'is_active'     => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ooopps',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $template = TemplateSurat::findOrFail($id);

            $data = $validator->validated();

            $data['variabel'] = $this->extractVariables(
                $data['isi_template']
            );

            $data['is_active'] = $request->boolean(
                'is_active',
                true
            );

            $template->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Template surat berhasil diperbarui',
                'data' => $template,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template surat tidak ditemukan',
                'data' => null,
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui template surat, ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $template = TemplateSurat::findOrFail($id);

            if (\App\Models\SuratKeluar::where('template_surat_id', $template->id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template masih dipakai surat keluar. Nonaktifkan saja supaya tidak muncul di form.',
                    'data' => null,
                ], 422);
            }

            $template->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Template surat berhasil dihapus',
                'data' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template surat tidak ditemukan',
                'data' => null,
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus template surat, ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}

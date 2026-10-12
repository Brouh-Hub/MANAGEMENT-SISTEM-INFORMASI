<?php

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Models\SuratKeluar;
use App\Models\SuratKeluarField;
use App\Models\TemplateSurat;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SuratKeluar::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    $map = [
                        'draft'            => ['Draft', 'secondary'],
                        'menunggu_approve' => ['Menunggu Persetujuan', 'warning'],
                        'terbit'           => ['Disetujui', 'success'],
                    ];
                    [$label, $color] = $map[$row->status] ?? ['-', 'secondary'];

                    return '<span class="text-' . $color . '"><i class="fa fa-circle" style="font-size:8px;"></i> ' . $label . '</span>';
                })
                ->addColumn('actions', function ($row) {

                    $detail = htmlspecialchars(json_encode($row));

                    // BUAT CONTAINER TOMBOL TERLEBIH DAHULU
                    $btn = '<div class="btn-group">';

                    // TOMBOL LIHAT
                    $btn .= '<a href="javascript:void(0)"
                        class="btn-detail btn btn-sm btn-light"
                        data-detail="' . $detail . '"
                        title="Lihat">
                        <i class="fa fa-eye"></i>
                    </a>';

                    // TOMBOL UBAH
                    $btn .= '<a href="javascript:void(0)"
                        class="btn-update btn btn-sm btn-light"
                        data-detail="' . $detail . '"
                        data-id="' . $row->id . '"
                        title="Ubah">
                        <i class="fa fa-pen"></i>
                    </a>';

                    // TOMBOL AJUKAN: KHUSUS TU DAN STATUS DRAFT
                    if (
                        auth()->user()?->jabatan === 'Tata Usaha' &&
                        $row->status === 'draft'
                    ) {
                        $btn .= '<a href="javascript:void(0)"
                            class="btn-ajukan btn btn-sm btn-light text-primary"
                            data-id="' . $row->id . '"
                            title="Ajukan Persetujuan">
                            <i class="fa fa-paper-plane"></i>
                        </a>';
                    }

                    // TOMBOL SETUJUI: KHUSUS KEPALA SEKOLAH
                    // Tombol setujui: hanya Kepala Sekolah
                    if (
                        $row->status === 'menunggu_approve' &&
                        auth()->user()?->jabatan === 'Kepala Sekolah'
                    ) {
                        $btn .= '<a href="javascript:void(0)"
                            class="btn-approve btn btn-sm btn-light text-success"
                            data-id="' . $row->id . '"
                            title="Setujui Surat">
                            <i class="fa fa-check"></i>
                        </a>';
                    }
                    // TOMBOL HAPUS
                    $btn .= '<a href="javascript:void(0)"
                        class="btn-delete btn btn-sm btn-light text-danger"
                        data-id="' . $row->id . '"
                        title="Hapus">
                        <i class="fa fa-trash"></i>
                    </a>';

                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        $templates = TemplateSurat::with([
            'fields' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('urutan')
                    ->orderBy('id');
            }
        ])
            ->where('is_active', true)
            ->orderBy('nama_template')
            ->get();

        return view('admin.surat-keluar.index', [
            'title'     => 'Surat Keluar',
            'subtitle'  => 'Kelola dan pantau surat yang telah keluar oleh sekolah.',
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        $templates = TemplateSurat::with([
            'fields' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('urutan')
                    ->orderBy('id');
            }
        ])
            ->where('is_active', true)
            ->orderBy('nama_template')
            ->get();

        return view('admin.surat-keluar.create', [
            'title' => 'Tambah Surat Keluar',
            'subtitle' => 'Pilih template dan isi data surat keluar.',
            'templates' => $templates,
        ]);
    }

    /**
     * Ambil hanya input custom yang key-nya memang field custom aktif
     */
    private function cleanCustom(Request $request): array
    {
        $allowed = SuratKeluarField::where('is_default', false)
            ->where('is_active', true)
            ->pluck('key')
            ->all();

        $input = $request->input('custom', []);

        return array_intersect_key(is_array($input) ? $input : [], array_flip($allowed));
    }

    /**
     * Ambil hanya data input yang memang terdaftar
     * sebagai field aktif pada template yang dipilih.
     */
    private function cleanTemplateData(Request $request): array
    {
        $templateId = $request->input('template_surat_id');

        if (!$templateId) {
            return [];
        }

        $template = TemplateSurat::with([
            'fields' => function ($query) {
                $query->where('is_active', true);
            }
        ])->find($templateId);

        if (!$template) {
            return [];
        }

        $allowed = $template->fields
            ->pluck('key')
            ->all();

        $input = $request->input('template_data', []);

        return array_intersect_key(
            is_array($input) ? $input : [],
            array_flip($allowed)
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_surat_id' => 'nullable|exists:template_surat,id',
            'isi_surat'         => 'nullable|string',
            'nomor_surat'       => 'required|string|max:255',
            'perihal'           => 'required|string|max:255',
            'tujuan'            => 'required|string|max:255',
            'alamat_tujuan'     => 'nullable|string',
            'keterangan'        => 'nullable|string',
            'tanggal_surat'     => 'required|date',
            'file_surat'        => 'nullable|file|mimes:pdf|max:5120',
            'custom'            => 'nullable|array',
            'custom.*'          => 'nullable|string|max:1000',
            'template_data'     => 'nullable|array',
            'template_data.*'   => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();
            $data['user_id_pembuat'] = auth()->id();
            $data['status'] = 'draft';

            $dataTambahan = $this->cleanCustom($request);
            $templateData = $this->cleanTemplateData($request);

            if (!empty($templateData)) {
                $dataTambahan['_template'] = $templateData;
            }

            $data['data_tambahan'] = $dataTambahan;

            if ($request->hasFile('file_surat')) {
                $data['file_path'] = $request->file('file_surat')->store('surat-keluar', 'public');
            }

            unset(
                $data['file_surat'],
                $data['custom'],
                $data['template_data']
            );

            $surat = SuratKeluar::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil simpan surat keluar',
                'data' => [
                    'id' => $surat->id,
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal simpan surat keluar, ' . $e->getMessage(), 'data' => null], 500);
        }
    }

    public function show(string $id) {}
    public function edit(string $id) {}

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nomor_surat'    => 'required|string|max:255',
            'perihal'        => 'required|string|max:255',
            'tujuan'         => 'required|string|max:255',
            'alamat_tujuan'  => 'nullable|string',
            'keterangan'     => 'nullable|string',
            'tanggal_surat'  => 'required|date',
            'file_surat'     => 'nullable|file|mimes:pdf|max:5120',
            'custom'         => 'nullable|array',
            'custom.*'       => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $surat = SuratKeluar::findOrFail($id);
            $data = $validator->validated();

            // merge supaya nilai field yang sedang nonaktif tidak hilang
            $data['data_tambahan'] = array_merge($surat->data_tambahan ?? [], $this->cleanCustom($request));

            if ($request->hasFile('file_surat')) {
                $data['file_path'] = $request->file('file_surat')->store('surat-keluar', 'public');
            }
            unset($data['file_surat'], $data['custom']);

            $surat->update($data);

            return response()->json(['status' => 'success', 'message' => 'Berhasil ubah surat keluar', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Surat keluar tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal ubah surat keluar, ' . $e->getMessage(), 'data' => null], 500);
        }
    }

    public function approve(Request $request, string $id)
    {
        try {
            $surat = SuratKeluar::findOrFail($id);
            $surat->update([
                'status'            => 'terbit',
                'kepala_sekolah_id' => auth()->id(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Surat berhasil di-approve', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Surat keluar tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal approve surat, ' . $e->getMessage(), 'data' => null], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $surat = SuratKeluar::findOrFail($id);
            $surat->delete();

            return response()->json(['status' => 'success', 'message' => 'Berhasil hapus surat keluar', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Surat keluar tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal hapus surat keluar, ' . $e->getMessage(), 'data' => null], 500);
        }
    }


    public function generatePdf(string $id)
{
    try {

        $surat = SuratKeluar::findOrFail($id);

        /*
         * Surat Undangan menggunakan HTML yang sudah dibuat
         * dari form dan disimpan ke kolom isi_surat.
         */
        if (empty($surat->isi_surat)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Isi surat belum tersedia. Silakan isi dan simpan surat terlebih dahulu.',
            ], 422);
        }

        $html = $surat->isi_surat;

        /*
         * Ubah gambar lokal /images/... menjadi base64
         * agar logo tetap tampil di DomPDF.
         */
        $html = preg_replace_callback(
            '/src=["\']\/images\/([^"\']+)["\']/i',
            function ($match) {

                $imagePath = public_path(
                    'images/' . $match[1]
                );

                if (!file_exists($imagePath)) {
                    return $match[0];
                }

                $mime = mime_content_type($imagePath);

                $base64 = base64_encode(
                    file_get_contents($imagePath)
                );

                return 'src="data:' . $mime . ';base64,' . $base64 . '"';
            },
            $html
        );

        $pdf = Pdf::loadView(
            'admin.surat-keluar.pdf',
            [
                'isiSurat' => $html,
            ]
        )->setPaper('a4', 'portrait');

        $directory = 'surat-keluar/generated';

        Storage::disk('public')->makeDirectory(
            $directory
        );

        $filename = 'surat-' . $surat->id . '.pdf';

        $path = $directory . '/' . $filename;

        Storage::disk('public')->put(
            $path,
            $pdf->output()
        );

        $surat->update([
            'isi_surat' => $html,
            'file_path' => $path,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'PDF surat berhasil dibuat.',
            'preview_url' => route(
                'surat-keluar.preview',
                $surat->id
            ),
            'download_url' => route(
                'surat-keluar.download',
                $surat->id
            ),
        ]);

    } catch (ModelNotFoundException $e) {

        return response()->json([
            'status' => 'error',
            'message' => 'Surat keluar tidak ditemukan.',
        ], 404);

    } catch (Exception $e) {

        return response()->json([
            'status' => 'error',
            'message' =>
                'Gagal membuat PDF, ' .
                $e->getMessage(),
        ], 500);
    }
}

    public function download($id)
    {
        $surat = SuratKeluar::findOrFail($id);

        if (
            !$surat->file_path ||
            !Storage::disk('public')->exists($surat->file_path)
        ) {
            abort(404, 'PDF surat belum tersedia.');
        }

        $nomor = preg_replace(
            '/[^A-Za-z0-9_-]+/',
            '-',
            $surat->nomor_surat ?: (string) $surat->id
        );

        $filename = 'surat-' . trim($nomor, '-') . '.pdf';

        return Storage::disk('public')->download(
            $surat->file_path,
            $filename
        );
    }

    public function preview($id)
    {
        $surat = SuratKeluar::findOrFail($id);

        if (!$surat->file_path || !Storage::disk('public')->exists($surat->file_path)) {
            abort(404, 'File surat belum diunggah.');
        }

        // ditampilkan inline (bukan download), mime type otomatis
        return Storage::disk('public')->response($surat->file_path);
    }

    public function ajukan(string $id)
{
    try {
        $surat = SuratKeluar::findOrFail($id);

        if ($surat->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya surat berstatus Draft yang dapat diajukan.',
            ], 422);
        }

        if (
            !$surat->file_path ||
            !Storage::disk('public')->exists($surat->file_path)
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Buat PDF surat terlebih dahulu sebelum mengajukan persetujuan.',
            ], 422);
        }

        $surat->update([
            'status' => 'menunggu_approve',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Surat berhasil diajukan kepada Kepala Sekolah.',
        ]);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Surat keluar tidak ditemukan.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengajukan surat: ' . $e->getMessage(),
        ], 500);
    }
}
}

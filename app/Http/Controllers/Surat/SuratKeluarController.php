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
use App\Services\RenderSurat;
use App\Models\DokumenKerjasama;
use App\Models\NomorSuratReservasi;
use App\Models\PenomoranSurat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class SuratKeluarController extends Controller
{
    /*
     * Field bawaan form surat keluar yang selalu disimpan,
     * walaupun tidak didaftarkan sebagai field template.
     */
    private const FIELD_STANDAR = [
        'sifat',
        'lampiran',
        'isi_surat',
        'hari_tanggal_acara',
        'pukul',
        'tempat',
        'isi_penutup',
    ];

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SuratKeluar::with('pengonfirmasi:id,name')->latest('id')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    $warna = [
                        'draft'              => 'badge-arsip',
                        'menunggu_kepala_tu' => 'badge-info',
                        'dikembalikan'       => 'badge-ditolak',
                        'menunggu_approve'   => 'badge-diproses',
                        'terbit'             => 'badge-selesai',
                    ];
                    $label = SuratKeluar::STATUS[$row->status] ?? '-';
                    $color = $warna[$row->status] ?? 'badge-arsip';

                    $html = '<span class="badge-status dot ' . $color . '">' . $label . '</span>';

                    // Alasan dari Kepala Tata Usaha
                    if ($row->status === 'dikembalikan' && $row->alasan_konfirmasi) {
                        $html .= '<div class="small text-danger mt-1" style="max-width:220px; white-space:normal;">' . e($row->alasan_konfirmasi) . '</div>';
                    }

                    return $html;
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

                    $adalahTu = auth()->user()?->jabatan === 'Tata Usaha';

                    // TOMBOL UBAH: hanya TU, selama surat masih Draft / Dikembalikan
                    if ($adalahTu && in_array($row->status, ['draft', 'dikembalikan'], true)) {
                        $btn .= '<a href="javascript:void(0)"
                            class="btn-update btn btn-sm btn-light"
                            data-detail="' . $detail . '"
                            data-id="' . $row->id . '"
                            title="Ubah">
                            <i class="fa fa-pen"></i>
                        </a>';
                    }

                    // TOMBOL AJUKAN KE KEPALA TU: KHUSUS TU, STATUS DRAFT / DIKEMBALIKAN
                    if (
                        auth()->user()?->jabatan === 'Tata Usaha' &&
                        in_array($row->status, ['draft', 'dikembalikan'], true)
                    ) {
                        $btn .= '<a href="javascript:void(0)"
                            class="btn-ajukan btn btn-sm btn-light text-primary"
                            data-id="' . $row->id . '"
                            title="' . ($row->status === 'dikembalikan' ? 'Ajukan Ulang ke Kepala TU' : 'Ajukan ke Kepala TU') . '">
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
                    // TOMBOL HAPUS: hanya TU, hanya surat Draft
                    if ($adalahTu && $row->status === 'draft') {
                        $btn .= '<a href="javascript:void(0)"
                            class="btn-delete btn btn-sm btn-light text-danger"
                            data-id="' . $row->id . '"
                            title="Hapus">
                            <i class="fa fa-trash"></i>
                        </a>';
                    }

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
        $allowed = self::FIELD_STANDAR;

        $templateId = $request->input('template_surat_id');

        if ($templateId) {
            $template = TemplateSurat::with([
                'fields' => function ($query) {
                    $query->where('is_active', true);
                }
            ])->find($templateId);

            if ($template) {
                $allowed = array_merge($allowed, $template->fields->pluck('key')->all());
            }
        }

        $input = $request->input('template_data', []);

        $data = array_intersect_key(
            is_array($input) ? $input : [],
            array_flip($allowed)
        );

        /*
         * Form di modal "Tambah Surat" mengirim field bawaan langsung
         * (sifat, lampiran, isi_surat_input, ...), bukan template_data[...].
         * Ambil juga dari sana supaya tetap tersimpan.
         */
        foreach (self::FIELD_STANDAR as $key) {
            $sumber = $key === 'isi_surat' ? 'isi_surat_input' : $key;

            if (! isset($data[$key]) && $request->filled($sumber) && is_string($request->input($sumber))) {
                $data[$key] = $request->input($sumber);
            }
        }

        return $data;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_surat_id' => 'nullable|exists:template_surat,id',
            'isi_surat'         => 'nullable|string',
            // Nomor wajib hasil "Generate Nomor" (lihat generateNomor)
            'nomor_surat'       => 'required|string|max:255|unique:surat_keluar,nomor_surat',
            'perihal'           => 'required|string|max:255',
            'tujuan'            => 'nullable|string|max:255',
            'alamat_tujuan'     => 'nullable|string',
            'keterangan'        => 'nullable|string',
            'tanggal_surat'     => 'required|date',
            'file_surat'        => 'nullable|file|mimes:pdf|max:5120',
            'custom'            => 'nullable|array',
            'custom.*'          => 'nullable|string|max:1000',
            'template_data'     => 'nullable|array',
            'template_data.*'   => 'nullable|string|max:5000',
        ], [
            'nomor_surat.required' => 'Klik "Generate Nomor" terlebih dahulu.',
            'nomor_surat.unique'   => 'Nomor surat ini sudah dipakai surat lain. Generate ulang nomornya.',
        ]);

        // Nomor harus benar-benar hasil generate oleh TU yang sedang login
        $validator->after(function ($v) use ($request) {
            if (! $request->filled('nomor_surat')) {
                return;
            }

            $reservasi = NomorSuratReservasi::where('nomor_surat', $request->input('nomor_surat'))->first();

            if (! $reservasi || $reservasi->surat_keluar_id || $reservasi->user_id !== auth()->id()) {
                $v->errors()->add('nomor_surat', 'Nomor surat tidak valid. Gunakan tombol "Generate Nomor".');
            } elseif ((int) $reservasi->template_surat_id !== (int) $request->input('template_surat_id')) {
                $v->errors()->add('nomor_surat', 'Nomor ini dibuat untuk jenis surat lain. Generate ulang nomornya.');
            }
        });

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();
            $data['user_id_pembuat'] = auth()->id();
            $data['tujuan'] = filled($data['tujuan'] ?? null) ? $data['tujuan'] : '-';
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

            $surat = DB::transaction(function () use ($data) {
                $surat = SuratKeluar::create($data);

                // Tandai nomor sudah dipakai surat ini
                NomorSuratReservasi::where('nomor_surat', $surat->nomor_surat)
                    ->update(['surat_keluar_id' => $surat->id]);

                return $surat;
            });

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
            'perihal'        => 'required|string|max:255',
            'tujuan'         => 'nullable|string|max:255',
            'alamat_tujuan'  => 'nullable|string',
            'keterangan'     => 'nullable|string',
            'tanggal_surat'  => 'required|date',
            'file_surat'     => 'nullable|file|mimes:pdf|max:5120',
            'custom'         => 'nullable|array',
            'custom.*'       => 'nullable|string|max:1000',
            'template_data'   => 'nullable|array',
            'template_data.*' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $surat = SuratKeluar::findOrFail($id);

            if (! in_array($surat->status, ['draft', 'dikembalikan'], true)) {
                return response()->json(['status' => 'error', 'message' => 'Surat yang sedang diperiksa atau sudah terbit tidak bisa diubah.', 'data' => null], 422);
            }

            $data = $validator->validated();
            $data['tujuan'] = filled($data['tujuan'] ?? null) ? $data['tujuan'] : '-';

            // merge supaya nilai field yang sedang nonaktif tidak hilang
            $data['data_tambahan'] = array_merge($surat->data_tambahan ?? [], $this->cleanCustom($request));

            if ($request->has('template_data')) {
                $request->merge(['template_surat_id' => $surat->template_surat_id]);
                $data['data_tambahan']['_template'] = array_merge(
                    $surat->data_tambahan['_template'] ?? [],
                    $this->cleanTemplateData($request)
                );
            }

            if ($request->hasFile('file_surat')) {
                if ($surat->file_path) {
                    Storage::disk('public')->delete($surat->file_path);
                }
                $data['file_path'] = $request->file('file_surat')->store('surat-keluar', 'public');
            }
            unset($data['file_surat'], $data['custom'], $data['template_data']);

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

            if ($surat->status !== 'menunggu_approve') {
                return response()->json(['status' => 'error', 'message' => 'Surat ini tidak sedang menunggu persetujuan.', 'data' => null], 422);
            }

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

            if ($surat->status !== 'draft') {
                return response()->json(['status' => 'error', 'message' => 'Hanya surat berstatus Draft yang bisa dihapus.', 'data' => null], 422);
            }

            if ($surat->file_path) {
                Storage::disk('public')->delete($surat->file_path);
            }
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

        $surat = SuratKeluar::with('templateSurat')->findOrFail($id);

        if (! in_array($surat->status, ['draft', 'dikembalikan'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'PDF hanya bisa dibuat ulang untuk surat Draft atau Dikembalikan.',
            ], 422);
        }

        /*
         * Isi surat dibuat di server dari template + data yang tersimpan,
         * supaya nomor, tanggal, dan tanda tangan selalu terisi.
         * Kalau surat tidak memakai template, pakai isi_surat yang tersimpan.
         */
        $html = $surat->templateSurat
            ? app(RenderSurat::class)->dariSurat($surat)
            : (string) $surat->isi_surat;

        if (trim(strip_tags($html, '<img>')) === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Isi surat belum tersedia. Silakan pilih template dan simpan surat terlebih dahulu.',
            ], 422);
        }

        // Simpan versi tanpa base64 ke database (lebih ringan)
        $isiTersimpan = $html;

        // Logo & gambar lokal dijadikan base64 supaya tampil di DomPDF
        $html = app(RenderSurat::class)->sematkanGambar($html);

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
            'isi_surat' => $isiTersimpan,
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

    /*
     * POST /surat-keluar/generate-nomor
     * TU memasukkan password login-nya sendiri, lalu sistem membuat nomor surat
     * berikutnya sesuai format di Pengaturan Surat > Penomoran.
     * Nomor langsung dicatat (unik), jadi dua TU tidak mungkin dapat nomor yang sama.
     */
    public function generateNomor(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'template_surat_id' => 'nullable|exists:template_surat,id',
            'password'          => 'required|string',
        ], [
            'password.required' => 'Masukkan password akun Anda.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        // Maksimal 5 kali salah, lalu dikunci 5 menit
        $kunci = 'generate-nomor:' . $user->id;

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            $menit = (int) ceil(RateLimiter::availableIn($kunci) / 60);

            return response()->json([
                'status'  => 'error',
                'message' => "Terlalu banyak percobaan. Coba lagi dalam {$menit} menit.",
                'errors'  => ['password' => ["Terlalu banyak percobaan. Coba lagi dalam {$menit} menit."]],
            ], 429);
        }

        if (! Hash::check($request->input('password'), $user->password)) {
            RateLimiter::hit($kunci, 300);
            $sisa = RateLimiter::remaining($kunci, 5);

            return response()->json([
                'status'  => 'error',
                'message' => 'Password salah.',
                'errors'  => ['password' => ["Password salah. Sisa percobaan: {$sisa}."]],
            ], 422);
        }

        RateLimiter::clear($kunci);

        $templateId = $request->filled('template_surat_id') ? (int) $request->input('template_surat_id') : null;

        // Semua dalam satu transaksi dengan baris aturan terkunci, supaya dua klik / dua TU
        // tidak mendapat nomor yang sama.
        [$reservasi, $dipakaiUlang] = DB::transaction(function () use ($templateId, $user) {
            $aturan = PenomoranSurat::whereKey(PenomoranSurat::untukTemplate($templateId)->id)
                ->lockForUpdate()
                ->first();

            // Nomor yang pernah di-generate TU ini tapi suratnya belum jadi disimpan -> pakai lagi,
            // asal nomornya benar-benar masih bebas dan sesuai format aturan yang berlaku sekarang.
            $kandidat = NomorSuratReservasi::where('user_id', $user->id)
                ->whereNull('surat_keluar_id')
                ->where('template_surat_id', $templateId)
                ->whereYear('created_at', now()->year)
                ->oldest('id')
                ->get()
                ->first(fn ($r) => ! SuratKeluar::where('nomor_surat', $r->nomor_surat)->exists()
                    && ! DokumenKerjasama::where('nomor_dokumen', $r->nomor_surat)->exists()
                    && $aturan->susun((int) $r->nomor_urut, $r->created_at) === $r->nomor_surat);

            if ($kandidat) {
                return [$kandidat, true];
            }

            [$nomor, $urut, $aturanId] = PenomoranSurat::ambilNomorBaru($templateId);

            return [NomorSuratReservasi::create([
                'nomor_surat'        => $nomor,
                'nomor_urut'         => $urut,
                'penomoran_surat_id' => $aturanId,
                'template_surat_id'  => $templateId,
                'user_id'            => $user->id,
            ]), false];
        });

        return response()->json([
            'status'  => 'success',
            'message' => $dipakaiUlang
                ? 'Nomor yang sebelumnya belum terpakai digunakan lagi.'
                : 'Nomor surat berhasil dibuat.',
            'data'    => ['nomor_surat' => $reservasi->nomor_surat],
        ]);
    }

    /*
     * TU mengajukan surat ke Kepala Tata Usaha (dari Draft atau setelah Dikembalikan).
     * Setelah Kepala TU setuju, surat lanjut ke Kepala Sekolah.
     */
    public function ajukan(string $id)
    {
        try {
            $surat = SuratKeluar::findOrFail($id);

            if (! in_array($surat->status, ['draft', 'dikembalikan'], true)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Hanya surat berstatus Draft atau Dikembalikan yang dapat diajukan.',
                ], 422);
            }

            // Surat dari template: PDF dibuat ulang supaya Kepala TU melihat versi terbaru
            if ($surat->template_surat_id) {
                $hasil = $this->generatePdf($id);

                if ($hasil->getStatusCode() !== 200) {
                    return $hasil;
                }

                $surat->refresh();
            }

            if (! $surat->file_path || ! Storage::disk('public')->exists($surat->file_path)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Buat PDF surat terlebih dahulu sebelum mengajukan persetujuan.',
                ], 422);
            }

            $diajukanUlang = $surat->status === 'dikembalikan';

            $surat->update([
                'status'            => 'menunggu_kepala_tu',
                'alasan_konfirmasi' => null,
                'dikonfirmasi_oleh' => null,
                'dikonfirmasi_at'   => null,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => $diajukanUlang
                    ? 'Surat diajukan ulang kepada Kepala Tata Usaha.'
                    : 'Surat berhasil diajukan kepada Kepala Tata Usaha.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Surat keluar tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengajukan surat: ' . $e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Kerjasama;
use App\Models\Sertifikat;
use App\Models\Siswa;
use App\Models\Pkl;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use Yajra\DataTables\Facades\DataTables;

class SertifikatController extends Controller
{
    private const STATUS_DRAFT = 'draft';
    private const STATUS_TERBIT = 'terbit';

    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

    private function rules(?string $ignoreId = null): array
    {
        $pkl = 'required_if:template_key,pkl';

        return [
            'kerjasama_id' => [
                'nullable',
                'exists:' . Kerjasama::class . ',id',
            ],

            'template_key' => [
                'required',
                Rule::in(
                    array_keys(
                        config('sertifikat.templates', [])
                    )
                ),
            ],

            'nama_penerima' => [
                'required',
                'string',
                'max:100',
            ],

            'judul' => [
                'required_unless:template_key,pkl',
                'nullable',
                'string',
                'max:255',
            ],

            'deskripsi' => [
                'nullable',
                'string',
                'max:500',
            ],

            'tanggal_terbit' => [
                'required',
                'date',
            ],

            'nomor_sertifikat' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique(
                    'sertifikat',
                    'nomor_sertifikat'
                )->ignore($ignoreId),
            ],

            'nis' => [
                $pkl,
                'nullable',
                'string',
                'max:30',
            ],

            'tempat_lahir' => [
                $pkl,
                'nullable',
                'string',
                'max:100',
            ],

            'tanggal_lahir' => [
                $pkl,
                'nullable',
                'date',
            ],

            'konsentrasi' => [
                $pkl,
                'nullable',
                'string',
                'max:150',
            ],

            'mitra' => [
                $pkl,
                'nullable',
                'string',
                'max:200',
            ],

            'predikat' => [
                $pkl,
                'nullable',
                Rule::in([
                    'Biasa',
                    'Cukup Baik',
                    'Sangat Baik',
                ]),
            ],

            'pimpinan_jabatan' => [
                $pkl,
                'nullable',
                'string',
                'max:100',
            ],

            'pimpinan_nama' => [
                $pkl,
                'nullable',
                'string',
                'max:150',
            ],

            'pimpinan_nip' => [
                $pkl,
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALISASI
    |--------------------------------------------------------------------------
    */

    private function normalisasi(array $v): array
    {
        if (
            ($v['template_key'] ?? null) === 'pkl'
            &&
            blank($v['judul'] ?? null)
        ) {
            $v['judul'] = 'Praktik Kerja Lapangan (PKL)';
        }

        return $v;
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Siswa::query();

            $search = $request->input('search.value');

            if ($search) {
                $query->where(function ($q) use ($search) {

                    $q->where(
                        'nis',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'nama',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'kelas',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'jurusan',
                        'like',
                        '%' . $search . '%'
                    );
                });
            }

            return DataTables::of($query)

                ->addIndexColumn()

                ->addColumn('nis', function ($siswa) {
                    return $siswa->nis ?? '-';
                })

                ->addColumn('nama', function ($siswa) {
                    return $siswa->nama ?? '-';
                })

                ->addColumn('kelas', function ($siswa) {
                    return $siswa->kelas ?? '-';
                })

                ->addColumn('jurusan', function ($siswa) {

                    return Siswa::deteksiJurusan(
                        $siswa->jurusan ?? '',
                        $siswa->kelas ?? ''
                    ) ?? (
                        $siswa->jurusan ?? '-'
                    );
                })

                ->addColumn('actions', function ($siswa) {

                    return '
                        <div class="d-flex gap-1">

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary btn-detail-siswa"
                                data-id="' . $siswa->id . '">

                                <i class="fa fa-eye me-1"></i>
                                Detail

                            </button>

                            <form
                                action="' . route(
                                    'sertifikat.generate',
                                    ['id' => $siswa->id]
                                ) . '"
                                method="POST"
                                target="_blank"
                                class="d-inline">

                                ' . csrf_field() . '

                                <select
                                    name="predikat"
                                    class="form-select form-select-sm d-inline-block w-auto"
                                    required>

                                    <option value="" selected disabled>Pilih Grade</option>
                                    <option value="Biasa">Biasa</option>
                                    <option value="Cukup Baik">Cukup Baik</option>
                                    <option value="Sangat Baik">Sangat Baik</option>

                                </select>

                                <button
                                    type="submit"
                                    class="btn btn-sm text-white"
                                    style="background:var(--brand);">

                                    <i class="fa fa-certificate me-1"></i>
                                    Generate Sertifikat

                                </button>

                            </form>

                        </div>
                    ';
                })

                ->rawColumns([
                    'actions'
                ])

                ->make(true);
        }

        $kerjasamaList = Kerjasama::orderBy(
            'judul_kerjasama'
        )->get();

        $templates = config(
            'sertifikat.templates',
            []
        );

        return view(
            'admin.sertifikat.index',
            compact(
                'kerjasamaList',
                'templates'
            )
        );
    }

    public function store(
        Request $request
    ) {

        $validator = Validator::make(
            $request->all(),
            $this->rules()
        );

        if ($validator->fails()) {

            return response()->json([
                'message' => 'Ooopps',
                'errors' =>
                    $validator->errors(),
            ], 422);
        }

        try {

            $v = $this->normalisasi(
                $validator->validated()
            );

            $v['nomor_sertifikat'] =
                filled(
                    $v['nomor_sertifikat']
                    ?? null
                )
                    ? trim(
                        $v['nomor_sertifikat']
                    )
                    : $this->buatNomor(
                        $v['tanggal_terbit']
                    );

            Sertifikat::create(
                $v + [
                    'user_id_penerbit' =>
                        auth()->id(),

                    'status' =>
                        self::STATUS_DRAFT,
                ]
            );

            return response()->json([
                'message' =>
                    'Sertifikat disimpan sebagai draft. Klik Generate untuk membuat PDF.',
            ], 201);

        } catch (Exception $e) {

            return response()->json([
                'message' =>
                    'Gagal menyimpan sertifikat, ' .
                    $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        string $id
    ) {

        $validator = Validator::make(
            $request->all(),
            $this->rules($id)
        );

        if ($validator->fails()) {

            return response()->json([
                'message' => 'Ooopps',
                'errors' =>
                    $validator->errors(),
            ], 422);
        }

        try {

            $sertifikat =
                Sertifikat::findOrFail($id);

            $v = $this->normalisasi(
                $validator->validated()
            );

            if (
                blank(
                    $v['nomor_sertifikat']
                    ?? null
                )
            ) {

                unset(
                    $v['nomor_sertifikat']
                );
            }

            $fileLama =
                $sertifikat->file_path;

            $sertifikat->update($v);

            if (
                $sertifikat->status ===
                self::STATUS_TERBIT
            ) {

                $this->renderPdf(
                    $sertifikat
                );

                if (
                    $fileLama
                    &&
                    $fileLama !==
                    $sertifikat->file_path
                ) {

                    Storage::disk('public')
                        ->delete(
                            $fileLama
                        );
                }
            }

            return response()->json([
                'message' =>
                    'Sertifikat berhasil diperbarui.',
            ]);

        } catch (
            ModelNotFoundException $e
        ) {

            return response()->json([
                'message' =>
                    'Sertifikat tidak ditemukan',
            ], 404);

        } catch (Exception $e) {

            return response()->json([
                'message' =>
                    'Gagal memperbarui sertifikat, ' .
                    $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE
    |--------------------------------------------------------------------------
    */

    public function generate(
        Request $request,
        string $id
    ) {

        $request->validate([
            'predikat' => [
                'required',
                Rule::in(['Biasa', 'Cukup Baik', 'Sangat Baik']),
            ],
        ]);

        try {

            $siswa =
                Siswa::findOrFail($id);

            $pkl = Pkl::with(
                'kerjasama.mitra'
            )
                ->where(
                    'siswa_id',
                    $siswa->id
                )
                ->latest('id')
                ->first();

            if (!$pkl) {

                return back()->with(
                    'error',
                    'Data PKL siswa belum tersedia.'
                );
            }

            $tanggalTerbit =
                Carbon::today()->format(
                    'Y-m-d'
                );

            $sertifikat =
                Sertifikat::where(
                    'nis',
                    $siswa->nis
                )
                    ->where(
                        'template_key',
                        'pkl'
                    )
                    ->latest('id')
                    ->first();

            if (!$sertifikat) {

                $sertifikat =
                    Sertifikat::create([

                        'nomor_sertifikat' =>
                            $this->buatNomor(
                                $tanggalTerbit
                            ),

                        'judul' =>
                            'Praktik Kerja Lapangan (PKL)',

                        'deskripsi' =>
                            $pkl->keterangan,

                        'tanggal_terbit' =>
                            $tanggalTerbit,

                        'kerjasama_id' =>
                            $pkl->kerjasama_id,

                        'user_id_penerima' =>
                            null,

                        'user_id_penerbit' =>
                            auth()->id(),

                        'template_id' =>
                            null,

                        'template_key' =>
                            'pkl',

                        'status' =>
                            self::STATUS_DRAFT,

                        'nama_penerima' =>
                            $siswa->nama,

                        'nis' =>
                            $siswa->nis,

                        'tempat_lahir' =>
                            $siswa->tempat_lahir,

                        'tanggal_lahir' =>
                            $siswa->tanggal_lahir,

                        'konsentrasi' =>
                            Siswa::deteksiJurusan(
                                $siswa->jurusan ?? '',
                                $siswa->kelas ?? ''
                            ) ?? (
                                $siswa->jurusan ?? '-'
                            ),

                        'mitra' =>
                            $pkl->kerjasama?->mitra?->nama_perusahaan
                            ?? '-',

                        'predikat' =>
                            $request->predikat,

                        'pimpinan_jabatan' =>
                            'Kepala Sekolah',

                        'pimpinan_nama' =>
                            $pkl->pembimbing_pkl,

                        'pimpinan_nip' =>
                            $pkl->pembimbing_nip,
                    ]);

            } else {

                $sertifikat->update([

                    'judul' =>
                        'Praktik Kerja Lapangan (PKL)',

                    'deskripsi' =>
                        $pkl->keterangan,

                    'tanggal_terbit' =>
                        $sertifikat->tanggal_terbit
                        ?: $tanggalTerbit,

                    'kerjasama_id' =>
                        $pkl->kerjasama_id,

                    'nama_penerima' =>
                        $siswa->nama,

                    'nis' =>
                        $siswa->nis,

                    'tempat_lahir' =>
                        $siswa->tempat_lahir,

                    'tanggal_lahir' =>
                        $siswa->tanggal_lahir,

                    'konsentrasi' =>
                        Siswa::deteksiJurusan(
                            $siswa->jurusan ?? '',
                            $siswa->kelas ?? ''
                        ) ?? (
                            $siswa->jurusan ?? '-'
                        ),

                    'mitra' =>
                        $pkl->kerjasama?->mitra?->nama_perusahaan
                        ?? '-',

                    'predikat' =>
                        $request->predikat,

                    'pimpinan_jabatan' =>
                        $sertifikat->pimpinan_jabatan
                        ?: 'Kepala Sekolah',

                    'pimpinan_nama' =>
                        $pkl->pembimbing_pkl
                        ?: $sertifikat->pimpinan_nama,

                    'pimpinan_nip' =>
                        $pkl->pembimbing_nip
                        ?: $sertifikat->pimpinan_nip,
                ]);
            }

            $this->renderPdf(
                $sertifikat->fresh()
            );

            $sertifikat->refresh();

            return response()->file(
                storage_path(
                    'app/public/' .
                    $sertifikat->file_path
                )
            );

        } catch (
            ModelNotFoundException $e
        ) {

            return back()->with(
                'error',
                'Data siswa tidak ditemukan.'
            );

        } catch (Exception $e) {

            return back()->with(
                'error',
                'Gagal generate sertifikat: ' .
                $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(
        string $id
    ) {

        try {

            $sertifikat =
                Sertifikat::findOrFail($id);

            if (
                $sertifikat->file_path
            ) {

                Storage::disk('public')
                    ->delete(
                        $sertifikat->file_path
                    );
            }

            $sertifikat->delete();

            return response()->json([
                'message' =>
                    'Sertifikat dihapus.',
            ]);

        } catch (
            ModelNotFoundException $e
        ) {

            return response()->json([
                'message' =>
                    'Sertifikat tidak ditemukan',
            ], 404);

        } catch (Exception $e) {

            return response()->json([
                'message' =>
                    'Gagal hapus sertifikat, ' .
                    $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NOMOR SERTIFIKAT
    |--------------------------------------------------------------------------
    */

    private function buatNomor(
        string $tanggal
    ): string {

        $tgl = Carbon::parse(
            $tanggal
        );

        $romawi = [
            'I',
            'II',
            'III',
            'IV',
            'V',
            'VI',
            'VII',
            'VIII',
            'IX',
            'X',
            'XI',
            'XII',
        ][$tgl->month - 1];

        $urut =
            Sertifikat::whereYear(
                'tanggal_terbit',
                $tgl->year
            )->count();

        do {

            $urut++;

            $nomor = sprintf(
                '%03d/SERT/%s/%d',
                $urut,
                $romawi,
                $tgl->year
            );

        } while (
            Sertifikat::where(
                'nomor_sertifikat',
                $nomor
            )->exists()
        );

        return $nomor;
    }

    /*
    |--------------------------------------------------------------------------
    | PLACEHOLDER
    |--------------------------------------------------------------------------
    */

    private function replacePlaceholders(
        string $text,
        Sertifikat $sertifikat
    ): string {

        $ttl = '-';

        if (
            $sertifikat->tempat_lahir
            &&
            $sertifikat->tanggal_lahir
        ) {

            $ttl =
                $sertifikat->tempat_lahir .
                ', ' .
                Carbon::parse(
                    $sertifikat->tanggal_lahir
                )->translatedFormat(
                    'd F Y'
                );
        }

        $data = [

            '{{nama}}' =>
                $sertifikat->nama_penerima ?? '-',

            '{{nama_penerima}}' =>
                $sertifikat->nama_penerima ?? '-',

            '{{nis}}' =>
                $sertifikat->nis ?? '-',

            '{{ttl}}' =>
                $ttl,

            '{{tempat_lahir}}' =>
                $sertifikat->tempat_lahir ?? '-',

            '{{tanggal_lahir}}' =>
                $sertifikat->tanggal_lahir
                    ? Carbon::parse(
                        $sertifikat->tanggal_lahir
                    )->translatedFormat(
                        'd F Y'
                    )
                    : '-',

            '{{konsentrasi}}' =>
                $sertifikat->konsentrasi ?? '-',

            '{{tempat_pkl}}' =>
                $sertifikat->mitra ?? '-',

            '{{mitra}}' =>
                $sertifikat->mitra ?? '-',

            '{{predikat}}' =>
                $sertifikat->predikat ?? '-',

            '{{nomor_sertifikat}}' =>
                $sertifikat->nomor_sertifikat ?? '-',

            '{{tanggal_terbit}}' =>
                $sertifikat->tanggal_terbit
                    ? Carbon::parse(
                        $sertifikat->tanggal_terbit
                    )->translatedFormat(
                        'd F Y'
                    )
                    : '-',

            '{{kepala_jabatan}}' =>
                $sertifikat->pimpinan_jabatan ?? '-',

            '{{pimpinan_jabatan}}' =>
                $sertifikat->pimpinan_jabatan ?? '-',

            '{{kepala_nama}}' =>
                $sertifikat->pimpinan_nama ?? '-',

            '{{pimpinan_nama}}' =>
                $sertifikat->pimpinan_nama ?? '-',

            '{{kepala_nip}}' =>
                $sertifikat->pimpinan_nip ?? '-',

            '{{pimpinan_nip}}' =>
                $sertifikat->pimpinan_nip ?? '-',
        ];

        return str_replace(
            array_keys($data),
            array_values($data),
            $text
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER PDF
    |--------------------------------------------------------------------------
    */

    private function renderPdf(
        Sertifikat $sertifikat
    ): void {

        $template = config(
            "sertifikat.templates.{$sertifikat->template_key}"
        );

        if (!$template) {

            throw new Exception(
                'Template belum dipilih atau tidak ada di config.'
            );
        }

        $file = public_path(
            $template['image']
        );

        if (!file_exists($file)) {

            throw new Exception(
                'Gambar template belum ada: ' .
                $template['image']
            );
        }

        $ext = strtolower(
            pathinfo(
                $file,
                PATHINFO_EXTENSION
            )
        );

        $mime =
            $ext === 'jpg'
                ? 'jpeg'
                : $ext;

        $bg =
            'data:image/' .
            $mime .
            ';base64,' .
            base64_encode(
                file_get_contents($file)
            );

        $fields =
            $template['fields'] ?? [];

        $isi = [

            'nomor_sertifikat' =>
                $sertifikat->nomor_sertifikat,

            'judul' =>
                $sertifikat->judul,

            'nama_penerima' =>
                $sertifikat->nama_penerima,

            'deskripsi' =>
                $sertifikat->deskripsi,

            'tanggal_terbit' =>
                $sertifikat->tanggal_terbit,

            'nis' =>
                $sertifikat->nis,

            'ttl' =>
                (
                    $sertifikat->tempat_lahir
                    &&
                    $sertifikat->tanggal_lahir
                )
                    ? $sertifikat->tempat_lahir .
                        ', ' .
                        Carbon::parse(
                            $sertifikat->tanggal_lahir
                        )->translatedFormat(
                            'd F Y'
                        )
                    : '-',

            'konsentrasi' =>
                $sertifikat->konsentrasi,

            'mitra' =>
                $sertifikat->mitra,

            'predikat' =>
                $sertifikat->predikat,

            'mitra_predikat' =>
                trim(
                    ($sertifikat->mitra ?? '') .
                    ' - ' .
                    ($sertifikat->predikat ?? '')
                ),

            'pimpinan_jabatan' =>
                $sertifikat->pimpinan_jabatan,

            'pimpinan_nama' =>
                $sertifikat->pimpinan_nama,

            'pimpinan_nip' =>
                $sertifikat->pimpinan_nip,
        ];

        foreach (
            $fields as $key => &$field
        ) {

            if (
                isset($field['text'])
                &&
                is_string(
                    $field['text']
                )
            ) {

                $field['text'] =
                    $this->replacePlaceholders(
                        $field['text'],
                        $sertifikat
                    );
            }

            if (
                isset($field['field'])
                &&
                !isset($field['text'])
            ) {

                $field['text'] =
                    $isi[
                        $field['field']
                    ] ?? '';
            }
        }

        unset($field);

        $pdf = Pdf::loadView(
            'admin.sertifikat.pdf',
            [
                'sertifikat' =>
                    $sertifikat,

                'posisi' =>
                    $fields,

                'bg' =>
                    $bg,

                'isi' =>
                    $isi,
            ]
        )
            ->setPaper(
                'a4',
                'landscape'
            );

        $path =
            'sertifikat/' .
            Str::slug(
                $sertifikat->nomor_sertifikat
            ) .
            '.pdf';

        Storage::disk('public')
            ->put(
                $path,
                $pdf->output()
            );

        $sertifikat->update([
            'file_path' =>
                $path,

            'status' =>
                self::STATUS_TERBIT,
        ]);
    }
}

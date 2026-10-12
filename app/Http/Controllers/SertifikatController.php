<?php

namespace App\Http\Controllers;

use App\Models\Kerjasama;
use App\Models\Sertifikat;
use App\Models\Siswa;
use App\Models\Pkl;
use App\Models\Mitra;

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
                    'Sangat Baik',
                    'Baik',
                    'Cukup',
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

    /*
    |--------------------------------------------------------------------------
    | DETAIL SISWA
    |--------------------------------------------------------------------------
    */

    public function detailSiswa($id)
    {
        $siswa = Siswa::findOrFail($id);

        $pkl = Pkl::with(
            'kerjasama.mitra'
        )
            ->where(
                'siswa_id',
                $siswa->id
            )
            ->latest('id')
            ->first();

        $sertifikat = Sertifikat::where(
            'nis',
            $siswa->nis
        )
            ->latest('id')
            ->first();

        return response()->json([

            'siswa' => [
                'id' => $siswa->id,
                'nis' => $siswa->nis ?? '-',
                'nama' => $siswa->nama ?? '-',

                'tempat_lahir' =>
                    $siswa->tempat_lahir ?? '-',

                'tanggal_lahir' =>
                    $siswa->tanggal_lahir
                        ? Carbon::parse(
                            $siswa->tanggal_lahir
                        )->format('d-m-Y')
                        : '-',

                'kelas' => $siswa->kelas ?? '-',
                'jurusan' => $siswa->jurusan ?? '-',

                'konsentrasi' =>
                    Siswa::deteksiJurusan(
                        $siswa->jurusan ?? '',
                        $siswa->kelas ?? ''
                    ) ?? (
                        $siswa->jurusan ?? '-'
                    ),
            ],

            'pkl' => $pkl ? [

                'tempat_pkl' =>
                    $pkl->kerjasama?->mitra?->nama_perusahaan
                    ?? '-',

                'pembimbing_pkl' =>
                    $pkl->pembimbing_pkl ?? '-',

                'tanggal_mulai' =>
                    $pkl->tanggal_mulai
                        ? Carbon::parse(
                            $pkl->tanggal_mulai
                        )->format('d-m-Y')
                        : '-',

                'tanggal_selesai' =>
                    $pkl->tanggal_selesai
                        ? Carbon::parse(
                            $pkl->tanggal_selesai
                        )->format('d-m-Y')
                        : '-',

                'status' =>
                    $pkl->status ?? '-',

                'keterangan' =>
                    $pkl->keterangan ?? '-',

            ] : null,

            'sertifikat' => $sertifikat ? [

                'status' =>
                    $sertifikat->status ?? '-',

                'nomor' =>
                    $sertifikat->nomor_sertifikat ?? '-',

                'tanggal_terbit' =>
                    $sertifikat->tanggal_terbit
                        ? Carbon::parse(
                            $sertifikat->tanggal_terbit
                        )->format('d-m-Y')
                        : '-',

            ] : null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DAFTAR SISWA
    |--------------------------------------------------------------------------
    */

public function daftarSiswa(Request $request)
{
    $query = Siswa::query();

    if ($request->filled('search.value')) {
        $search = $request->input('search.value');

        $query->where(function ($q) use ($search) {
            $q->where('nis', 'like', '%' . $search . '%')
                ->orWhere('nama', 'like', '%' . $search . '%')
                ->orWhere('kelas', 'like', '%' . $search . '%')
                ->orWhere('jurusan', 'like', '%' . $search . '%');
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
            ) ?? ($siswa->jurusan ?? '-');
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
                        action="' . route('sertifikat.generate', ['id' => $siswa->id]) . '"
                        method="POST"
                        target="_blank"
                        class="d-inline">

                        ' . csrf_field() . '

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

        ->rawColumns(['actions'])
        ->make(true);
}



    /*
    |--------------------------------------------------------------------------
    | TEMPLATE IMPORT
    |--------------------------------------------------------------------------
    */

    public function templateSiswa()
    {
        $isi =
            "\xEF\xBB\xBF" .
            "NIS,Nama,Tempat Lahir,Tanggal Lahir,Kelas,Jurusan,Tempat PKL,Kepala/Pembimbing PKL,Tanggal Mulai,Tanggal Selesai,Keterangan\r\n" .
            "0001,Contoh Nama Siswa,Tanjungpinang,2008-01-01,XII RPL,Rekayasa Perangkat Lunak,PT Contoh Indonesia,Bapak Pembimbing,2026-01-05,2026-03-31,Selesai PKL\r\n";

        return response(
            $isi,
            200,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',

                'Content-Disposition' =>
                    'attachment; filename="template-import-siswa-pkl.csv"',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALISASI TANGGAL
    |--------------------------------------------------------------------------
    */

    private function normalisasiTanggalImport(
        $value
    ): ?string {

        if (
            $value === null ||
            trim((string) $value) === ''
        ) {
            return null;
        }

        try {

            if (is_numeric($value)) {

                return Carbon::create(
                    1899,
                    12,
                    30
                )
                    ->addDays((int) $value)
                    ->format('Y-m-d');
            }

            return Carbon::parse($value)
                ->format('Y-m-d');

        } catch (Exception $e) {

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT SISWA + PKL
    |--------------------------------------------------------------------------
    */
public function importSiswa(Request $request)
{
    $request->validate([
        'file' => [
            'required',
            'file',
            'mimes:csv,txt,xlsx,xls',
            'max:5120',
        ],
    ]);

    $file = $request->file('file');

    $ext = strtolower(
        $file->getClientOriginalExtension()
    );

    /*
    |--------------------------------------------------------------------------
    | BACA FILE
    |--------------------------------------------------------------------------
    */

    try {

        $rows = in_array(
            $ext,
            ['xlsx', 'xls']
        )
            ? $this->bacaExcel($file)
            : $this->bacaCsv(
                $file->getRealPath()
            );

    } catch (Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI DATA
    |--------------------------------------------------------------------------
    */

    if (count($rows) < 2) {

        return response()->json([
            'success' => false,
            'message' =>
                'File Excel kosong atau tidak memiliki data.',
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    |
    | bacaExcel() sudah mengembalikan data mulai dari baris header.
    |
    */

    $header = array_map(
        function ($h) {

            return mb_strtolower(
                trim(
                    preg_replace(
                        '/\s+/',
                        ' ',
                        str_replace(
                            "\xEF\xBB\xBF",
                            '',
                            (string) $h
                        )
                    )
                )
            );
        },
        $rows[0]
    );

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    $dataRows = array_slice(
        $rows,
        1
    );

    /*
    |--------------------------------------------------------------------------
    | CARI KOLOM
    |--------------------------------------------------------------------------
    */

    $cari = function (
        array $alias
    ) use ($header) {

        foreach ($alias as $nama) {

            $nama =
                mb_strtolower(
                    trim($nama)
                );

            $index = array_search(
                $nama,
                $header,
                true
            );

            if ($index !== false) {
                return $index;
            }
        }

        return null;
    };

    /*
    |--------------------------------------------------------------------------
    | MAPPING KOLOM
    |--------------------------------------------------------------------------
    */

    $idx = [

        'no' => $cari([
            'no',
            'nomor',
        ]),

        'nama' => $cari([
            'nama siswa',
            'nama siswa ',
            'nama',
            'nama lengkap',
        ]),

        'kelas' => $cari([
            'kelas',
            'kelas siswa',
        ]),

        'jurusan' => $cari([
            'program keahlian',
            'jurusan',
            'konsentrasi keahlian',
        ]),

        'tempat_pkl' => $cari([
            'tempat pkl',
            'dudi/instansi',
            'dudi',
            'instansi',
            'perusahaan',
            'tempat praktik',
        ]),

        'alamat_pkl' => $cari([
            'alamat',
            'alamat dudi/instansi',
            'alamat dudi',
            'alamat instansi',
        ]),

        'pembimbing_pkl' => $cari([
            'nama pembimbing',
            'pembimbing',
            'pembimbing pkl',
            'kepala/pembimbing pkl',
        ]),

        'nis' => $cari([
            'nis',
            'nomor induk siswa',
            'nomor induk',
        ]),

        'tanggal_mulai' => $cari([
            'tanggal mulai',
            'tgl mulai',
            'tanggal mulai pkl',
        ]),

        'tanggal_selesai' => $cari([
            'tanggal selesai',
            'tgl selesai',
            'tanggal selesai pkl',
        ]),

        'keterangan' => $cari([
            'keterangan',
            'ket',
        ]),
    ];

    /*
    |--------------------------------------------------------------------------
    | VALIDASI HEADER WAJIB
    |--------------------------------------------------------------------------
    */

    if ($idx['nama'] === null) {

        return response()->json([
            'success' => false,
            'message' =>
                'Kolom Nama Siswa tidak ditemukan.',
            'header_terbaca' =>
                $header,
        ], 422);
    }

    if ($idx['tempat_pkl'] === null) {

        return response()->json([
            'success' => false,
            'message' =>
                'Kolom Tempat PKL tidak ditemukan.',
            'header_terbaca' =>
                $header,
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | HASIL
    |--------------------------------------------------------------------------
    */

    $hasil = [

        'baru' => 0,

        'diperbarui' => 0,

        'pkl_baru' => 0,

        'pkl_diperbarui' => 0,

        'mitra_baru' => 0,

        'kerjasama_baru' => 0,

        'dilewati' => 0,

        'catatan' => [],
    ];

    /*
    |--------------------------------------------------------------------------
    | PEMBIMBING AKTIF
    |--------------------------------------------------------------------------
    |
    | Contoh:
    |
    | M. AMIRUDDIN | NUR SALSABILA
    |               | SERLI MUJI ASTANTI
    |
    | Kedua siswa akan mendapatkan:
    | pembimbing_pkl = M. AMIRUDDIN
    |
    */

    $pembimbingAktif = '';

    /*
    |--------------------------------------------------------------------------
    | LOOP DATA
    |--------------------------------------------------------------------------
    */

    foreach (
        $dataRows as $no => $row
    ) {

        /*
        |--------------------------------------------------------------------------
        | HELPER NILAI
        |--------------------------------------------------------------------------
        */

        $nilai = function (
            $key
        ) use (
            $row,
            $idx
        ) {

            if (
                !isset($idx[$key])
                ||
                $idx[$key] === null
            ) {
                return '';
            }

            return trim(
                (string) (
                    $row[
                        $idx[$key]
                    ] ?? ''
                )
            );
        };

        /*
        |--------------------------------------------------------------------------
        | DATA DASAR
        |--------------------------------------------------------------------------
        */

        $nama = $nilai('nama');

        /*
        |--------------------------------------------------------------------------
        | LEWATI BARIS KOSONG
        |--------------------------------------------------------------------------
        */

        if ($nama === '') {

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | PEMBIMBING
        |--------------------------------------------------------------------------
        */

        $pembimbingBaris =
            $nilai(
                'pembimbing_pkl'
            );

        /*
        | Kalau ada pembimbing baru,
        | simpan sebagai pembimbing aktif.
        */

        if (
            $pembimbingBaris !== ''
        ) {

            $pembimbingAktif =
                $pembimbingBaris;
        }

        /*
        | Gunakan pembimbing aktif.
        */

        $pembimbing =
            $pembimbingAktif;

        /*
        |--------------------------------------------------------------------------
        | SISWA
        |--------------------------------------------------------------------------
        */

        $nis =
            $nilai('nis');

        $kelas =
            $nilai('kelas');

        $jurusan =
            $nilai('jurusan');

        /*
        |--------------------------------------------------------------------------
        | PKL
        |--------------------------------------------------------------------------
        */

        $tempatPkl =
            $nilai('tempat_pkl');

        $alamatPkl =
            $nilai('alamat_pkl');

        /*
        |--------------------------------------------------------------------------
        | TANGGAL
        |--------------------------------------------------------------------------
        */

        $tanggalMulai =
            $this->normalisasiTanggalImport(
                $nilai('tanggal_mulai')
            );

        $tanggalSelesai =
            $this->normalisasiTanggalImport(
                $nilai('tanggal_selesai')
            );

        /*
        |--------------------------------------------------------------------------
        | KETERANGAN
        |--------------------------------------------------------------------------
        */

        $keterangan =
            $nilai('keterangan');

        /*
        |--------------------------------------------------------------------------
        | CARI SISWA
        |--------------------------------------------------------------------------
        */

        $siswa = null;

        /*
        | Prioritas 1: NIS
        */

        if ($nis !== '') {

            $siswa =
                Siswa::where(
                    'nis',
                    $nis
                )->first();
        }

        /*
        | Prioritas 2: Nama
        */

        if (!$siswa) {

            $namaNormal =
                mb_strtolower(
                    preg_replace(
                        '/\s+/',
                        ' ',
                        trim($nama)
                    )
                );

            $siswa =
                Siswa::whereRaw(
                    "LOWER(
                        TRIM(
                            REPLACE(
                                REPLACE(nama, '  ', ' '),
                                '  ',
                                ' '
                            )
                        )
                    ) = ?",
                    [
                        $namaNormal,
                    ]
                )->first();
        }

        /*
        |--------------------------------------------------------------------------
        | JURUSAN
        |--------------------------------------------------------------------------
        */

        $jurusanUntukDeteksi =
            $jurusan;

        /*
        | Kalau siswa lama punya jurusan,
        | gunakan juga sebagai fallback.
        */

        if (
            $jurusanUntukDeteksi === ''
            &&
            $siswa
            &&
            $siswa->jurusan
        ) {

            $jurusanUntukDeteksi =
                $siswa->jurusan;
        }

        $jurusanBaku =
            Siswa::deteksiJurusan(
                $jurusanUntukDeteksi !== ''
                    ? $jurusanUntukDeteksi
                    : null,

                $kelas !== ''
                    ? $kelas
                    : null
            );

        /*
        |--------------------------------------------------------------------------
        | SISWA TIDAK DITEMUKAN → BUAT BARU
        |--------------------------------------------------------------------------
        */

        if (!$siswa) {

            if (!$jurusanBaku) {

                $hasil['dilewati']++;

                $hasil['catatan'][] =
                    'Baris ' .
                    ($no + 2) .
                    ': Jurusan tidak dapat ditentukan untuk "' .
                    $nama .
                    '" dengan kelas "' .
                    $kelas .
                    '".';

                continue;
            }

            $dataSiswaBaru = [

                'nama' =>
                    $nama,

                'kelas' =>
                    $kelas,

                'jurusan' =>
                    $jurusanBaku,
            ];

            /*
            | NIS hanya dimasukkan
            | kalau memang tersedia.
            */

            if ($nis !== '') {

                $dataSiswaBaru['nis'] =
                    $nis;
            }

            $siswa =
                Siswa::create(
                    $dataSiswaBaru
                );

            $hasil['baru']++;

        } else {

            /*
            |--------------------------------------------------------------------------
            | UPDATE SISWA LAMA
            |--------------------------------------------------------------------------
            */

            $dataSiswa = [

                'nama' =>
                    $nama,
            ];

            if ($kelas !== '') {

                $dataSiswa['kelas'] =
                    $kelas;
            }

            if ($jurusanBaku !== null) {

                $dataSiswa['jurusan'] =
                    $jurusanBaku;
            }

            if (
                $nis !== ''
                &&
                empty($siswa->nis)
            ) {

                $dataSiswa['nis'] =
                    $nis;
            }

            $siswa->update(
                $dataSiswa
            );

            $hasil['diperbarui']++;
        }

        /*
        |--------------------------------------------------------------------------
        | TEMPAT PKL WAJIB
        |--------------------------------------------------------------------------
        */

        if ($tempatPkl === '') {

            $hasil['dilewati']++;

            $hasil['catatan'][] =
                'Baris ' .
                ($no + 2) .
                ': Tempat PKL kosong untuk "' .
                $nama .
                '".';

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | MITRA
        |--------------------------------------------------------------------------
        */

        $mitra =
            Mitra::whereRaw(
                'LOWER(TRIM(nama_perusahaan)) = ?',
                [
                    mb_strtolower(
                        trim($tempatPkl)
                    ),
                ]
            )->first();

        /*
        |--------------------------------------------------------------------------
        | BUAT MITRA
        |--------------------------------------------------------------------------
        */

        if (!$mitra) {

            $mitra =
                Mitra::create([

                    'nama_perusahaan' =>
                        $tempatPkl,

                    'alamat' =>
                        $alamatPkl !== ''
                            ? $alamatPkl
                            : null,

                    'contact_person' =>
                        null,

                    'email' =>
                        null,

                    'website' =>
                        null,

                    'bidang' =>
                        'Dunia Industri',

                    'humas_id' =>
                        auth()->id(),
                ]);

            $hasil['mitra_baru']++;

        } else {

            if ($alamatPkl !== '') {

                $mitra->update([
                    'alamat' =>
                        $alamatPkl,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | KERJA SAMA
        |--------------------------------------------------------------------------
        */

        $kerjasama =
            Kerjasama::where(
                'industri_id',
                $mitra->id
            )
                ->where(
                    'jenis_kerjasama',
                    'PKL'
                )
                ->when(
                    $tanggalMulai,
                    fn ($q) =>
                        $q->whereDate(
                            'tanggal_mulai',
                            $tanggalMulai
                        )
                )
                ->when(
                    $tanggalSelesai,
                    fn ($q) =>
                        $q->whereDate(
                            'tanggal_selesai',
                            $tanggalSelesai
                        )
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | BUAT KERJA SAMA
        |--------------------------------------------------------------------------
        */

        if (!$kerjasama) {

            $kerjasama =
                Kerjasama::create([

                    'judul_kerjasama' =>
                        'Kerja Sama PKL - ' .
                        $mitra->nama_perusahaan,

                    'tanggal_mulai' =>
                        $tanggalMulai,

                    'tanggal_selesai' =>
                        $tanggalSelesai,

                    'jenis_kerjasama' =>
                        'PKL',

                    'status' =>
                        'aktif',

                    'industri_id' =>
                        $mitra->id,

                    'humas_id' =>
                        auth()->id(),

                    'kepala_sekolah_id' =>
                        null,

                    'file_mou_path' =>
                        null,
                ]);

            $hasil['kerjasama_baru']++;
        }

        /*
        |--------------------------------------------------------------------------
        | PKL
        |--------------------------------------------------------------------------
        */

        $pkl =
            Pkl::where(
                'kerjasama_id',
                $kerjasama->id
            )
                ->where(
                    'siswa_id',
                    $siswa->id
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | UPDATE PKL
        |--------------------------------------------------------------------------
        */

        if ($pkl) {

            $dataPkl = [];

            if ($tanggalMulai) {

                $dataPkl['tanggal_mulai'] =
                    $tanggalMulai;
            }

            if ($tanggalSelesai) {

                $dataPkl['tanggal_selesai'] =
                    $tanggalSelesai;
            }

            /*
            | INI YANG MENYIMPAN PEMBIMBING
            */

            if ($pembimbing !== '') {

                $dataPkl['pembimbing_pkl'] =
                    $pembimbing;
            }

            if ($keterangan !== '') {

                $dataPkl['keterangan'] =
                    $keterangan;
            }

            if (
                $tanggalMulai
                &&
                in_array(
                    'tahun',
                    (new Pkl)->getFillable()
                )
            ) {

                $dataPkl['tahun'] =
                    date(
                        'Y',
                        strtotime(
                            $tanggalMulai
                        )
                    );
            }

            if (!empty($dataPkl)) {

                $pkl->update(
                    $dataPkl
                );
            }

            $hasil['pkl_diperbarui']++;

        } else {

            /*
            |--------------------------------------------------------------------------
            | BUAT PKL BARU
            |--------------------------------------------------------------------------
            */

            $dataPkl = [

                'siswa_id' =>
                    $siswa->id,

                'kerjasama_id' =>
                    $kerjasama->id,

                'tanggal_mulai' =>
                    $tanggalMulai,

                'tanggal_selesai' =>
                    $tanggalSelesai,

                'pembimbing_pkl' =>
                    $pembimbing !== ''
                        ? $pembimbing
                        : null,

                'keterangan' =>
                    $keterangan !== ''
                        ? $keterangan
                        : 'Data dari Rekapan Pemetaan PKL',
            ];

            if (
                $tanggalMulai
                &&
                in_array(
                    'tahun',
                    (new Pkl)->getFillable()
                )
            ) {

                $dataPkl['tahun'] =
                    date(
                        'Y',
                        strtotime(
                            $tanggalMulai
                        )
                    );
            }

            Pkl::create(
                $dataPkl
            );

            $hasil['pkl_baru']++;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'success' =>
            true,

        'message' =>
            "Import selesai: " .
            "{$hasil['baru']} siswa baru, " .
            "{$hasil['diperbarui']} siswa diperbarui, " .
            "{$hasil['pkl_baru']} PKL baru, " .
            "{$hasil['pkl_diperbarui']} PKL diperbarui.",

        ...$hasil,

        'catatan' =>
            array_slice(
                $hasil['catatan'],
                0,
                20
            ),
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | CLEAR SEMUA DATA SISWA
    |--------------------------------------------------------------------------
    */

    public function clearSemuaSiswa()
    {
        try {

            $jumlah = Siswa::count();

            Siswa::query()->delete();

            return response()->json([

                'success' => true,

                'message' =>
                    "Berhasil menghapus {$jumlah} data siswa.",

            ]);

        } catch (Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Gagal menghapus semua data siswa: ' .
                    $e->getMessage(),

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CARI KOLOM
    |--------------------------------------------------------------------------
    */

    private function cariKolom(
        array $header,
        array $alias
    ): ?int {

        foreach ($alias as $a) {

            $i = array_search(
                $a,
                $header,
                true
            );

            if ($i !== false) {
                return $i;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | RAPIKAN NIS
    |--------------------------------------------------------------------------
    */

    private function rapikanNis($v): string
    {
        if (
            is_int($v) ||
            is_float($v)
        ) {
            return (string) (int) $v;
        }

        $v = trim(
            (string) $v
        );

        return preg_match(
            '/^\d+\.0+$/',
            $v
        )
            ? explode('.', $v)[0]
            : $v;
    }

    /*
    |--------------------------------------------------------------------------
    | BACA CSV
    |--------------------------------------------------------------------------
    */

    private function bacaCsv(
        string $path
    ): array {

        $isi = file_get_contents($path);

        $isi = preg_replace(
            '/^\xEF\xBB\xBF/',
            '',
            $isi
        );

        if (
            !mb_check_encoding(
                $isi,
                'UTF-8'
            )
        ) {

            $isi = mb_convert_encoding(
                $isi,
                'UTF-8',
                'Windows-1252'
            );
        }

        $baris = preg_split(
            '/\r\n|\r|\n/',
            $isi
        );

        $pertama =
            $baris[0] ?? '';

        $pemisah = collect([
            ',',
            ';',
            "\t",
        ])
            ->sortByDesc(
                fn ($d) =>
                    substr_count(
                        $pertama,
                        $d
                    )
            )
            ->first();

        $rows = [];

        foreach ($baris as $b) {

            if (trim($b) === '') {
                continue;
            }

            $rows[] = str_getcsv(
                $b,
                $pemisah
            );
        }

        return $rows;
    }

    /*
    |--------------------------------------------------------------------------
    | BACA EXCEL
    |--------------------------------------------------------------------------
    */

    private function bacaExcel($file): array
{
    $spreadsheet =
        \PhpOffice\PhpSpreadsheet\IOFactory::load(
            $file->getRealPath()
        );

    /*
    |--------------------------------------------------------------------------
    | CARI SHEET PEMBIMBING TERLEBIH DAHULU
    |--------------------------------------------------------------------------
    |
    | File Excel kamu memiliki banyak sheet.
    | Sheet2 adalah format:
    |
    | No | Nama Pembimbing | Nama Siswa | KELAS | Tempat PKL | Alamat
    |
    */

    $namaSheetPrioritas = [
        'Sheet2',
        'PEMBIMBING',
        'GABUNGAN',
    ];

    foreach ($namaSheetPrioritas as $namaSheet) {

        $sheet = $spreadsheet->getSheetByName(
            $namaSheet
        );

        if (!$sheet) {
            continue;
        }

        $rows = $sheet->toArray(
            null,
            true,
            true,
            false
        );

        foreach ($rows as $index => $row) {

            $isi = array_map(
                function ($value) {

                    return mb_strtolower(
                        trim(
                            preg_replace(
                                '/\s+/',
                                ' ',
                                str_replace(
                                    "\xEF\xBB\xBF",
                                    '',
                                    (string) $value
                                )
                            )
                        )
                    );
                },
                $row
            );

            $adaNamaSiswa =
                in_array(
                    'nama siswa',
                    $isi,
                    true
                );

            $adaNamaPembimbing =
                in_array(
                    'nama pembimbing',
                    $isi,
                    true
                )
                ||
                in_array(
                    'pembimbing',
                    $isi,
                    true
                );

            $adaKelas =
                in_array(
                    'kelas',
                    $isi,
                    true
                );

            $adaTempatPkl =
                in_array(
                    'tempat pkl',
                    $isi,
                    true
                );

            if (
                $adaNamaSiswa
                &&
                $adaNamaPembimbing
                &&
                $adaKelas
                &&
                $adaTempatPkl
            ) {

                return array_slice(
                    $rows,
                    $index
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FALLBACK
    |--------------------------------------------------------------------------
    |
    | Kalau Sheet2 tidak ada, cari sheet lain yang cocok.
    |
    */

    foreach (
        $spreadsheet->getWorksheetIterator()
        as $sheet
    ) {

        $rows = $sheet->toArray(
            null,
            true,
            true,
            false
        );

        foreach ($rows as $index => $row) {

            $isi = array_map(
                function ($value) {

                    return mb_strtolower(
                        trim(
                            preg_replace(
                                '/\s+/',
                                ' ',
                                str_replace(
                                    "\xEF\xBB\xBF",
                                    '',
                                    (string) $value
                                )
                            )
                        )
                    );
                },
                $row
            );

            if (
                in_array(
                    'nama siswa',
                    $isi,
                    true
                )
            ) {

                return array_slice(
                    $rows,
                    $index
                );
            }
        }
    }

    throw new \Exception(
        'Header Excel tidak ditemukan. Pastikan file memiliki kolom Nama Siswa.'
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
        string $id
    ) {

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
                            'Sangat Baik',

                        'pimpinan_jabatan' =>
                            'Kepala Sekolah',

                        'pimpinan_nama' =>
                            null,

                        'pimpinan_nip' =>
                            null,
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
                        $sertifikat->predikat
                        ?: 'Sangat Baik',

                    'pimpinan_jabatan' =>
                        $sertifikat->pimpinan_jabatan
                        ?: 'Kepala Sekolah',
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kerjasama;
use App\Models\Mitra;
use App\Models\Pkl;
use App\Models\Sertifikat;
use App\Models\Siswa;

use Carbon\Carbon;
use Exception;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Yajra\DataTables\Facades\DataTables;

class SiswaController extends Controller
{
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

        // Siswa tanpa NIS: cocokkan lewat nama, jangan sampai kena sertifikat siswa lain
        $sertifikat = Sertifikat::when(
            filled($siswa->nis),
            fn ($q) => $q->where('nis', $siswa->nis),
            fn ($q) => $q->whereNull('nis')->where('nama_penerima', $siswa->nama)
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

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger btn-hapus-siswa"
                        data-id="' . $siswa->id . '"
                        data-nama="' . e($siswa->nama) . '"
                        title="Hapus siswa">
                        <i class="fa fa-trash"></i>
                    </button>

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
        return response()->download(
            public_path('templates/template-import-siswa-pkl (1).xlsx'),
            'template-import-siswa-pkl.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALISASI TANGGAL
    |--------------------------------------------------------------------------
    |
    | Format yang didukung:
    | - Angka serial Excel        : 39459
    | - 12-01-2008 / 12/01/2008 / 12.01.2008  (hari-bulan-tahun)
    | - 2008-01-12                 (tahun-bulan-hari)
    | - 12 Januari 2008 / 12 Jan 2008 / 12 January 2008
    |
    */

    private function normalisasiTanggalImport(
        $value
    ): ?string {

        $value = trim((string) $value);

        if ($value === '' || $value === '-') {
            return null;
        }

        try {

            // Angka serial Excel
            if (is_numeric($value)) {
                return Carbon::create(1899, 12, 30)
                    ->addDays((int) $value)
                    ->format('Y-m-d');
            }

            // Tahun di depan: 2008-01-12 / 2008/01/12
            if (preg_match('/^(\d{4})[\-\/\.](\d{1,2})[\-\/\.](\d{1,2})$/', $value, $m)) {
                return checkdate((int) $m[2], (int) $m[3], (int) $m[1])
                    ? sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3])
                    : null;
            }

            // Hari di depan (format Indonesia): 12-01-2008 / 12/01/2008 / 12.01.2008
            if (preg_match('/^(\d{1,2})[\-\/\.](\d{1,2})[\-\/\.](\d{2,4})$/', $value, $m)) {
                $tahun = (int) $m[3];

                if ($tahun < 100) {
                    $tahun += $tahun > (int) date('y') ? 1900 : 2000;
                }

                return checkdate((int) $m[2], (int) $m[1], $tahun)
                    ? sprintf('%04d-%02d-%02d', $tahun, $m[2], $m[1])
                    : null;
            }

            // Nama bulan Indonesia → Inggris, lalu biarkan Carbon yang membaca
            $bulan = [
                'januari' => 'january', 'februari' => 'february', 'pebruari' => 'february',
                'maret' => 'march', 'april' => 'april', 'mei' => 'may',
                'juni' => 'june', 'juli' => 'july', 'agustus' => 'august',
                'september' => 'september', 'oktober' => 'october',
                'november' => 'november', 'nopember' => 'november',
                'desember' => 'december',
                'agt' => 'aug', 'agu' => 'aug', 'okt' => 'oct', 'des' => 'dec',
            ];

            $teks = mb_strtolower($value);

            foreach ($bulan as $id => $en) {
                $teks = preg_replace('/\b' . $id . '\b/u', $en, $teks);
            }

            return Carbon::parse($teks)->format('Y-m-d');

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

        'pembimbing_nip' => $cari([
            'nip pembimbing',
            'nip',
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

        // Data kelahiran siswa (opsional)
        'tempat_lahir' => $cari([
            'tempat lahir',
            'tempat lahir siswa',
            'tmp lahir',
            'tmp. lahir',
        ]),

        'tanggal_lahir' => $cari([
            'tanggal lahir',
            'tgl lahir',
            'tgl. lahir',
            'tanggal lahir siswa',
        ]),

        // Gabungan, contoh isi: "Tanjungpinang, 12 Januari 2008"
        'ttl' => $cari([
            'ttl',
            'tempat, tanggal lahir',
            'tempat tanggal lahir',
            'tempat/tanggal lahir',
            'tempat / tanggal lahir',
            'tempat / tgl. lahir',
            'tempat/tgl lahir',
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
    $nipPembimbingAktif = '';

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

            // NIP milik pembimbing sebelumnya tidak boleh terbawa.
            $nipPembimbingAktif = '';
        }

        $nipBaris = $nilai('pembimbing_nip');

        if ($nipBaris !== '') {

            $nipPembimbingAktif = $nipBaris;
        }

        /*
        | Gunakan pembimbing aktif.
        */

        $pembimbing =
            $pembimbingAktif;

        $pembimbingNip =
            $nipPembimbingAktif;

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
        | TEMPAT & TANGGAL LAHIR
        |--------------------------------------------------------------------------
        |
        | Bisa dari kolom terpisah (Tempat Lahir, Tanggal Lahir)
        | atau dari satu kolom TTL: "Tanjungpinang, 12 Januari 2008".
        |
        */
        $tempatLahir =
            $nilai('tempat_lahir');

        $tanggalLahirMentah =
            $nilai('tanggal_lahir');

        $ttl =
            $nilai('ttl');

        if (
            $ttl !== ''
            &&
            ($tempatLahir === '' || $tanggalLahirMentah === '')
        ) {
            $posisiKoma = mb_strrpos($ttl, ',');

            if ($posisiKoma !== false) {
                $tempatDariTtl = trim(mb_substr($ttl, 0, $posisiKoma));
                $tanggalDariTtl = trim(mb_substr($ttl, $posisiKoma + 1));
            } else {
                $tempatDariTtl = '';
                $tanggalDariTtl = $ttl;
            }

            if ($tempatLahir === '') {
                $tempatLahir = $tempatDariTtl;
            }

            if ($tanggalLahirMentah === '') {
                $tanggalLahirMentah = $tanggalDariTtl;
            }
        }

        $tanggalLahir =
            $this->normalisasiTanggalImport(
                $tanggalLahirMentah
            );

        // Tolak tanggal lahir yang tidak masuk akal
        // (misal angka asal "1" / "111" yang dibaca Excel sebagai tahun 1900).
        if (
            $tanggalLahir !== null
            &&
            (
                $tanggalLahir < '1950-01-01'
                ||
                $tanggalLahir > now()->format('Y-m-d')
            )
        ) {
            $tanggalLahir = null;
        }

        if (
            $tanggalLahirMentah !== ''
            &&
            $tanggalLahir === null
        ) {
            $hasil['catatan'][] =
                'Baris ' .
                ($no + 2) .
                ': Tanggal lahir "' .
                $tanggalLahirMentah .
                '" tidak dikenali, dikosongkan.';
        }

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

            if ($tempatLahir !== '') {
                $dataSiswaBaru['tempat_lahir'] =
                    $tempatLahir;
            }

            if ($tanggalLahir !== null) {
                $dataSiswaBaru['tanggal_lahir'] =
                    $tanggalLahir;
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

            // Isi/perbarui data lahir hanya kalau kolomnya diisi di file,
            // supaya data lama tidak terhapus oleh sel kosong.
            if ($tempatLahir !== '') {
                $dataSiswa['tempat_lahir'] =
                    $tempatLahir;
            }

            if ($tanggalLahir !== null) {
                $dataSiswa['tanggal_lahir'] =
                    $tanggalLahir;
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
        | TANGGAL PKL WAJIB (kolom kerja sama & PKL tidak boleh kosong)
        |--------------------------------------------------------------------------
        */

        if (!$tanggalMulai || !$tanggalSelesai) {

            $hasil['dilewati']++;

            $hasil['catatan'][] =
                'Baris ' .
                ($no + 2) .
                ': Tanggal mulai/selesai PKL kosong atau tidak terbaca untuk "' .
                $nama .
                '". Data PKL dilewati.';

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

            if ($pembimbingNip !== '') {

                $dataPkl['pembimbing_nip'] =
                    $pembimbingNip;
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

                'pembimbing_nip' =>
                    $pembimbingNip !== ''
                        ? $pembimbingNip
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

    /*
     * Hapus SATU siswa beserta data PKL-nya.
     * Kerja sama yang jadi tidak punya peserta ikut dihapus, lalu mitra yang
     * jadi tidak punya kerja sama (dan tidak punya dokumen MoU/PKS) ikut dihapus.
     * Sertifikat yang sudah diterbitkan tetap disimpan sebagai arsip.
     */
    public function hapusSiswa(string $id)
    {
        try {
            $siswa = Siswa::findOrFail($id);

            $hasil = DB::transaction(function () use ($siswa) {
                $kerjasamaIds = Pkl::where('siswa_id', $siswa->id)->pluck('kerjasama_id')->filter()->unique()->all();
                $mitraIds = Kerjasama::whereIn('id', $kerjasamaIds)->pluck('industri_id')->filter()->unique()->all();

                Pkl::where('siswa_id', $siswa->id)->delete();
                $siswa->delete();

                $kerjasamaKosong = Kerjasama::whereIn('id', $kerjasamaIds)->whereDoesntHave('pkl')->get();
                foreach ($kerjasamaKosong as $kerjasama) {
                    if ($kerjasama->file_mou_path) {
                        Storage::disk('public')->delete($kerjasama->file_mou_path);
                    }
                    $kerjasama->delete();
                }

                $mitraKosong = Mitra::whereIn('id', $mitraIds)
                    ->whereDoesntHave('kerjasama')
                    ->whereDoesntHave('dokumenKerjasama')
                    ->get();
                foreach ($mitraKosong as $mitra) {
                    if ($mitra->foto) {
                        Storage::disk('public')->delete($mitra->foto);
                    }
                    $mitra->delete();
                }

                return ['kerjasama' => $kerjasamaKosong->count(), 'mitra' => $mitraKosong->count()];
            });

            $tambahan = [];
            if ($hasil['kerjasama']) $tambahan[] = $hasil['kerjasama'] . ' kerja sama kosong';
            if ($hasil['mitra']) $tambahan[] = $hasil['mitra'] . ' mitra kosong';

            return response()->json([
                'status'  => 'success',
                'message' => 'Siswa ' . $siswa->nama . ' berhasil dihapus'
                    . ($tambahan ? ' (ikut dibersihkan: ' . implode(', ', $tambahan) . ').' : '.'),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan.'], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus siswa: ' . $e->getMessage()], 500);
        }
    }

    public function clearSemuaSiswa()
    {
        try {
            $hasil = DB::transaction(function () {

                /*
                 * Catat dulu kerja sama & mitra yang punya peserta PKL.
                 * Setelah siswa dihapus, hanya yang jadi KOSONG karena
                 * penghapusan ini yang ikut dihapus. Mitra/kerja sama yang
                 * dibuat manual dan memang belum punya siswa tidak disentuh.
                 */
                $kerjasamaIds = Pkl::query()->distinct()->pluck('kerjasama_id')->filter()->all();
                $mitraIds = Kerjasama::whereIn('id', $kerjasamaIds)
                    ->pluck('industri_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $jumlahSiswa = Siswa::count();

                // Data PKL dihapus eksplisit (tidak bergantung pada foreign key)
                Pkl::query()->delete();
                Siswa::query()->delete();

                // Kerja sama yang sekarang tidak punya peserta
                $kerjasamaKosong = Kerjasama::whereIn('id', $kerjasamaIds)
                    ->whereDoesntHave('pkl')
                    ->get();

                foreach ($kerjasamaKosong as $kerjasama) {
                    if ($kerjasama->file_mou_path) {
                        Storage::disk('public')->delete($kerjasama->file_mou_path);
                    }
                    $kerjasama->delete();
                }

                // Mitra industri yang sekarang tidak punya kerja sama
                $mitraKosong = Mitra::whereIn('id', $mitraIds)
                    ->whereDoesntHave('kerjasama')
                    ->whereDoesntHave('dokumenKerjasama') // mitra yang punya MoU/PKS tetap disimpan
                    ->get();

                foreach ($mitraKosong as $mitra) {
                    if ($mitra->foto) {
                        Storage::disk('public')->delete($mitra->foto);
                    }
                    $mitra->delete();
                }

                return [
                    'siswa'     => $jumlahSiswa,
                    'kerjasama' => $kerjasamaKosong->count(),
                    'mitra'     => $mitraKosong->count(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' =>
                    "Berhasil menghapus {$hasil['siswa']} data siswa beserta data PKL-nya, "
                    . "{$hasil['kerjasama']} kerja sama, dan {$hasil['mitra']} mitra industri yang terkait.",
                'data' => $hasil,
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

    /*
    | Sel Excel yang berformat tanggal diubah formatnya ke yyyy-mm-dd
    | sebelum dibaca, supaya tidak tertukar hari/bulan (format Amerika).
    */
    private function seragamkanFormatTanggal($sheet): void
    {
        foreach ($sheet->getCoordinates() as $koordinat) {
            $cell = $sheet->getCell($koordinat);

            if (
                is_numeric($cell->getValue())
                &&
                \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)
            ) {
                $sheet->getStyle($koordinat)
                    ->getNumberFormat()
                    ->setFormatCode('yyyy-mm-dd');
            }
        }
    }

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

        $this->seragamkanFormatTanggal($sheet);

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

        $this->seragamkanFormatTanggal($sheet);

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
                ||
                in_array(
                    'nama',
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
}

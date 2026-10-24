<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Pkl;
use App\Models\Kerjasama;
use App\Models\Mitra;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SiswaController extends Controller

{


public function index()

{

$kerjasama = Kerjasama::with('mitra')

    ->orderBy('judul_kerjasama')

    ->get();

return view('admin.siswa.index', [

    'title' => 'Data Siswa',

    'jurusan' => array_keys(config('jurusan')),

    'kerjasama' => $kerjasama,

]);

}


public function data()

{

$data = Siswa::orderBy('kelas')

    ->orderBy('nama')

    ->get()

    ->map(function ($siswa) {

        return [

            'id' => $siswa->id,

            'nis' => $siswa->nis,

            'nama' => $siswa->nama,

            'tempat_lahir' => $siswa->tempat_lahir ?? '',

            'tanggal_lahir' => $siswa->tanggal_lahir

                ? Carbon::parse($siswa->tanggal_lahir)->format('Y-m-d')

                : '',

            'kelas' => $siswa->kelas,

            'jurusan' => $siswa->jurusan,

            'konsentrasi' => Siswa::deteksiJurusan(

                $siswa->jurusan,

                $siswa->kelas

            ) ?? $siswa->jurusan,

        ];

    });

return response()->json([

    'data' => $data,

]);

}

public function store(Request $request)

{

$v = Validator::make($request->all(), [

    'nis' => 'required|unique\\\:siswa,nis',

    'nama' => 'required|string|max:255',

    'tempat_lahir' => 'nullable|string|max:100',

    'tanggal_lahir' => 'nullable|date',

    'kelas' => 'required|string|max:50',

    'jurusan' => 'required|in:' . implode(',', array_keys(config('jurusan'))),

]);

if ($v->fails()) {

    return response()->json([

        'success' => false,

        'errors' => $v->errors(),

    ], 422);

}

Siswa::create([

    'nis' => $request->nis,

    'nama' => $request->nama,

    'tempat_lahir' => $request->tempat_lahir,

    'tanggal_lahir' => $request->tanggal_lahir,

    'kelas' => $request->kelas,

    'jurusan' => $request->jurusan,

]);

return response()->json([

    'success' => true,

    'message' => 'Siswa ditambahkan',

]);

}


public function update(Request $request, Siswa $siswa)

{

$v = Validator::make($request->all(), [

    'nis' => 'required|unique\\\:siswa,nis,' . $siswa->id,

    'nama' => 'required|string|max:255',

    'tempat_lahir' => 'nullable|string|max:100',

    'tanggal_lahir' => 'nullable|date',

    'kelas' => 'required|string|max:50',

    'jurusan' => 'required|in:' . implode(',', array_keys(config('jurusan'))),

]);

if ($v->fails()) {

    return response()->json([

        'success' => false,

        'errors' => $v->errors(),

    ], 422);

}

$siswa->update([

    'nis' => $request->nis,

    'nama' => $request->nama,

    'tempat_lahir' => $request->tempat_lahir,

    'tanggal_lahir' => $request->tanggal_lahir,

    'kelas' => $request->kelas,

    'jurusan' => $request->jurusan,

]);

return response()->json([

    'success' => true,

    'message' => 'Siswa diperbarui',

]);

}


public function destroy(Siswa $siswa)

{

$siswa->delete();

return response()->json([

    'success' => true,

    'message' => 'Siswa dihapus',

]);

}




public function import(Request $request)
{
$validator = Validator::make($request->all(), [
'file' => ['required','file','mimes\\\:xlsx,xls,csv,txt','max:5120',],
// Fallback kalau data PKL di Excel kosong
'kerjasama_id' => ['nullable','exists\\\:kerjasama,id',],
'tanggal_mulai' => ['nullable','date',],
'tanggal_selesai' => ['nullable','date','after_or_equal\\\:tanggal_mulai',],
'keterangan' => ['nullable','string',],
]);

if ($validator->fails()) {
return response()->json([
    'success' => false,
    'message' => $validator->errors()->first(),
    'errors' => $validator->errors(),
], 422);

}

try {
$spreadsheet = IOFactory::load(
    $request->file('file')->getRealPath()
);

$rows = $spreadsheet
    ->getActiveSheet()
    ->toArray(null, true, true, false);
if (count($rows) < 2) {
    return response()->json([
        'success' => false,
        'message' => 'File Excel kosong atau tidak memiliki data.',
    ], 422);
}



$header = array_map(
    fn ($value) => $this->normalisasiHeader($value),
    array_shift($rows)
);

$kolom = $this->kolomImportSiswa($header);

if ($kolom['nis'] === null || $kolom['nama'] === null) {
    return response()->json([
        'success' => false,
        'message' => 'Kolom NIS dan Nama wajib ada di file Excel.',
        'header_terbaca' => $header,], 422);
}



$hasil = [
    'siswa_baru' => 0,
    'siswa_diperbarui' => 0,
    'mitra_baru' => 0,
    'kerjasama_baru' => 0,
    'pkl_ditambahkan' => 0,
    'pkl_dilewati' => 0,
    'dilewati' => 0,
    'catatan' => [],
];


foreach ($rows as $index => $row) {
    $baris = $index + 2;
    try {
        $data = $this->ambilDataImport(
            $row,
            $kolom,
            $request
        );

        if (
            $data['nis'] === '' &&
            $data['nama'] === ''
        ) {
            continue;
        }

        if ($data['nis'] === '') {
            throw new \Exception('NIS kosong.');
        }

        if ($data['nama'] === '') {
            throw new \Exception('Nama kosong.');
        }

        $siswa = Siswa::where(
            'nis',
            $data['nis']
        )->first();
        if ($siswa) {
            $siswa->update([
                'nama' => $data['nama'],
                'tempat_lahir' =>
                    $data['tempat_lahir']
                    ?: $siswa->tempat_lahir,
                'tanggal_lahir' =>
                    $data['tanggal_lahir']
                    ?: $siswa->tanggal_lahir,
                'kelas' =>
                    $data['kelas']
                    ?: $siswa->kelas,
                'jurusan' =>
                    $data['jurusan']
                    ?: $siswa->jurusan,
            ]);
            $hasil['siswa_diperbarui']++;
        } else {
            $siswa = Siswa::create([
                'nis' => $data['nis'],
                'nama' => $data['nama'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'kelas' => $data['kelas'],
                'jurusan' => $data['jurusan'],
            ]);
            $hasil['siswa_baru']++;
        }

        if (
            !$data['tanggal_mulai'] ||
            !$data['tanggal_selesai']

        ) {
            $hasil['pkl_dilewati']++;
            $hasil['catatan'][] =
                "Baris {$baris}: tanggal PKL {$data['nama']} belum lengkap.";
            continue;
        }

        $kerjasama = $this->resolveKerjasamaImport(
            $data,
            $request,
            $hasil
        );

        if (!$kerjasama) {
            $hasil['pkl_dilewati']++;
            $hasil['catatan'][] =
                "Baris {$baris}: Kerja Sama tidak ditemukan.";
            continue;
        }

        $pklAda = Pkl::where(
            'kerjasama_id',
            $kerjasama->id
        )
            ->where(
                'siswa_id',
                $siswa->id
            )
            ->exists();
        if ($pklAda) {
            $hasil['pkl_dilewati']++;
            $hasil['catatan'][] =
                "Baris {$baris}: {$data['nama']} sudah terdaftar di Kerja Sama tersebut.";
            continue;

        }
        $pkl = Pkl::create([
            'siswa_id' => $siswa->id,
            'kerjasama_id' => $kerjasama->id,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'pembimbing_pkl' => $data['pembimbing_pkl'],
            'keterangan' => $data['keterangan'],
        ]);

        $hasil['pkl_ditambahkan']++;
        } catch (\Throwable $e) {
            $hasil['dilewati']++;
            $hasil['catatan'][] =
                "Baris {$baris}: {$e->getMessage()}";
        }

}


return response()->json([
    'success' => true,
    'message' =>
        "{$hasil['siswa_baru']} siswa baru, "
        . "{$hasil['siswa_diperbarui']} siswa diperbarui, "
        . "{$hasil['mitra_baru']} mitra baru, "
        . "{$hasil['kerjasama_baru']} kerja sama baru, "
        . "{$hasil['pkl_ditambahkan']} peserta PKL ditambahkan.",
    ...$hasil,
]);

} catch (\Throwable $e) {

return response()->json([
    'success' => false,
    'message' => 'Import gagal: ' . $e->getMessage(),
], 422);
}
}

private function kolomImportSiswa(array $header): array

{
return [
'nis' => $this->cariKolom($header, [
    'nis',
    'nomor induk siswa',
    'nomor induk',
]),
'nama' => $this->cariKolom($header, [
    'nama',
    'nama siswa',
    'nama lengkap',
]),
'tempat_lahir' => $this->cariKolom($header, [
    'tempat lahir',
    'kota lahir',
]),

'tanggal_lahir' => $this->cariKolom($header, [
    'tanggal lahir',
    'tgl lahir',
    'tanggal_lahir',
]),
'kelas' => $this->cariKolom($header, [
    'kelas',
]),
'jurusan' => $this->cariKolom($header, [
    'jurusan',
    'konsentrasi',
    'konsentrasi keahlian',
]),
'tempat_pkl' => $this->cariKolom($header, [
    'tempat pkl',
    'tempat praktik',
    'tempat praktek',
    'perusahaan pkl',
    'mitra',
    'nama perusahaan',
]),
'pembimbing_pkl' => $this->cariKolom($header, [
    'kepala/pembimbing pkl',
    'kepala pembimbing pkl',
    'pembimbing pkl',
    'pembimbing',
    'guru pembimbing',
]),
'tanggal_mulai' => $this->cariKolom($header, [
    'tanggal mulai',
    'tgl mulai',
    'tanggal mulai pkl',
]),
'tanggal_selesai' => $this->cariKolom($header, [
    'tanggal selesai',
    'tgl selesai',
    'tanggal selesai pkl',
]),
'keterangan' => $this->cariKolom($header, [
    'keterangan',
    'ket',
    'catatan',
]),
];

}

private function normalisasiHeader($value): string

{

$value = (string) $value;

// Hapus BOM

$value = str_replace(
    "\xEF\xBB\xBF",
    '',
    $value
);

$value = trim(
    mb_strtolower($value)
);

$value = preg_replace(
    '/\*\***\s**\*\*+/',
    ' ',
    $value
);

return $value;

}



private function cariKolom(
array $header,
array $aliases
): ?int {
foreach ($aliases as $alias) {
    $alias = $this->normalisasiHeader($alias);
    $index = array_search(
        $alias,
        $header,
        true
    );
    if ($index !== false) {
        return $index;
    }

}

return null;

}


private function nilaiKolom(
array $row,
?int $index
) {
if ($index === null) {
    return null;
}
return $row[$index] ?? null;
}
private function rapikanNis($value): string
{
if ($value === null) {
    return '';
}
$value = trim((string) $value);

if (
    is_numeric($value)
    && str_ends_with($value, '.0')
) {
    $value = substr(
        $value,
        0,
        -2
    );
}
return $value;
}
private function normalisasiTanggal($value): ?string
{
if (
    $value === null
    || trim((string) $value) === ''
) {
    return null;
}

if (is_numeric($value)) {
    try {
        return \PhpOffice\PhpSpreadsheet\Shared\Date
            ::excelToDateTimeObject($value)
            ->format('Y-m-d');
    } catch (\Throwable $e) {
   // lanjut ke format string
    }    
}

$value = trim((string) $value);
$formats = [
    'd/m/Y',
    'd-m-Y',
    'Y-m-d',
    'Y/m/d',
    'm/d/Y',
    'd/m/y',
    'd-m-y',
];

foreach ($formats as $format) {
    try {
        return Carbon::createFromFormat(
            $format,
            $value
        )->format('Y-m-d');
    } catch (\Throwable $e) {
        // coba format berikutnya
    }

}



try {
    return Carbon::parse($value)
        ->format('Y-m-d');
} catch (\Throwable $e) {
    return null;
}

}

}

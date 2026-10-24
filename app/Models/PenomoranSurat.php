<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/*
| Aturan nomor surat keluar per template.
|
| Format memakai kode:
|   {nomor}  nomor urut (diberi nol di depan sesuai "panjang", mis. 007)
|   {kode}   kode klasifikasi (mis. 400.3.8)
|   {tahun}  2026      {bulan} 10      {romawi} X
|
| Nomor dibuat saat TU klik "Generate Nomor" (pakai password TU),
| lalu dicatat di tabel nomor_surat_reservasi supaya tidak pernah dobel.
*/
class PenomoranSurat extends Model
{
    protected $table = 'penomoran_surat';

    protected $fillable = [
        'template_surat_id',
        'kode',
        'format',
        'panjang',
        'reset',
        'nomor_terakhir',
        'periode',
    ];

    protected $casts = [
        'panjang'        => 'integer',
        'nomor_terakhir' => 'integer',
    ];

    /*
     * Format bawaan berdasarkan nama template (dipakai saat aturan
     * untuk template itu belum ada). Bisa diubah TU di Pengaturan Surat.
     */
    public const BAWAAN = [
        'Surat Keputusan'                 => ['kode' => null,      'format' => '{nomor} TAHUN {tahun}', 'panjang' => 1],
        'Surat Perintah Tugas'            => ['kode' => '400.3.8', 'format' => '{kode}/{nomor}/{tahun}'],
        'Surat Rekomendasi Mutasi Siswa'  => ['kode' => '421.5',   'format' => 'B/{kode}/{nomor}/I-SMKN4/{tahun}'],
        'Surat Undangan'                  => ['kode' => '005',     'format' => '{kode}/{nomor}/SMKN4-TPI/{tahun}'],
        'Surat Dinas Standar'             => ['kode' => '421',     'format' => '{kode}/{nomor}/SMKN4-TPI/{tahun}'],
    ];

    public const UMUM = ['kode' => '421', 'format' => '{kode}/{nomor}/SMKN4-TPI/{tahun}'];

    public function templateSurat()
    {
        return $this->belongsTo(TemplateSurat::class, 'template_surat_id');
    }

    /*
     * Aturan untuk template tertentu (null = surat tanpa template / Umum).
     * Kalau belum ada, dibuat dengan format bawaan.
     */
    public static function untukTemplate(?int $templateId): self
    {
        $aturan = static::where('template_surat_id', $templateId)->first();

        if ($aturan) {
            return $aturan;
        }

        $nama = $templateId ? TemplateSurat::whereKey($templateId)->value('nama_template') : null;
        $bawaan = static::BAWAAN[$nama] ?? static::UMUM;

        return static::create([
            'template_surat_id' => $templateId,
            'kode'              => $bawaan['kode'],
            'format'            => $bawaan['format'],
            'panjang'           => $bawaan['panjang'] ?? 3,
            'reset'             => 'tahunan',
            'nomor_terakhir'    => 0,
        ]);
    }

    public function periodeUntuk(Carbon $waktu): string
    {
        return match ($this->reset) {
            'bulanan' => $waktu->format('Y-m'),
            'tidak'   => '-',
            default   => $waktu->format('Y'),
        };
    }

    /*
     * Nomor urut berikutnya (tanpa menyimpan) — untuk contoh di halaman pengaturan.
     */
    public function urutBerikutnya(?Carbon $waktu = null): int
    {
        $waktu ??= now();

        return $this->periode === $this->periodeUntuk($waktu) || $this->reset === 'tidak'
            ? $this->nomor_terakhir + 1
            : 1;
    }

    public function susun(int $urut, ?Carbon $waktu = null): string
    {
        $waktu ??= now();
        $romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        return strtr($this->format, [
            '{nomor}'  => str_pad((string) $urut, max(1, (int) $this->panjang), '0', STR_PAD_LEFT),
            '{kode}'   => (string) $this->kode,
            '{tahun}'  => $waktu->format('Y'),
            '{bulan}'  => $waktu->format('m'),
            '{romawi}' => $romawi[(int) $waktu->format('n')],
        ]);
    }

    public function contoh(): string
    {
        return $this->susun($this->urutBerikutnya());
    }

    /*
     * Ambil nomor baru untuk template tertentu. WAJIB dipanggil di dalam
     * DB::transaction: baris aturan dikunci (lockForUpdate) supaya dua TU
     * yang generate bersamaan tidak mendapat nomor yang sama.
     *
     * @return array{0:string,1:int,2:int} [nomor surat, nomor urut, id aturan]
     */
    public static function ambilNomorBaru(?int $templateId): array
    {
        $id = static::untukTemplate($templateId)->id;
        $aturan = static::whereKey($id)->lockForUpdate()->first();

        $waktu = now();
        $periode = $aturan->periodeUntuk($waktu);
        $urut = $aturan->periode === $periode || $aturan->reset === 'tidak'
            ? $aturan->nomor_terakhir
            : 0;

        // Lewati nomor yang sudah dipakai / sudah dipesan (mis. surat lama yang diketik manual)
        do {
            $urut++;
            $nomor = $aturan->susun($urut, $waktu);
        } while (
            SuratKeluar::where('nomor_surat', $nomor)->exists()
            || NomorSuratReservasi::where('nomor_surat', $nomor)->exists()
        );

        $aturan->update([
            'nomor_terakhir' => $urut,
            'periode'        => $periode,
        ]);

        return [$nomor, $urut, $aturan->id];
    }
}

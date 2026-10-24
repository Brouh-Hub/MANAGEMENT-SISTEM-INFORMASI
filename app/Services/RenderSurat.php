<?php

namespace App\Services;

use App\Models\PengaturanSurat;
use App\Models\SuratKeluar;
use Carbon\Carbon;

/*
| Mengisi template surat keluar menjadi HTML siap cetak (DomPDF).
| Dipakai SuratKeluarController (PDF surat) dan PengaturanSuratController (contoh PDF).
*/
class RenderSurat
{
    /*
     * HTML surat dari template yang dipilih + data surat yang tersimpan.
     */
    public function dariSurat(SuratKeluar $surat): string
    {
        $tambahan = $surat->data_tambahan['_template'] ?? [];

        $nilai = array_merge(
            ['sifat' => 'Biasa', 'lampiran' => '-'],
            array_filter($tambahan, fn ($v) => $v !== null && $v !== ''),
            [
                'nomor_surat'   => $surat->nomor_surat,
                'tanggal_surat' => $surat->tanggal_surat,
                'perihal'       => $surat->perihal,
                'tujuan'        => $surat->tujuan,
                'alamat_tujuan' => $surat->alamat_tujuan,
                'keterangan'    => $surat->keterangan,
            ]
        );

        return $this->isiTemplate((string) $surat->templateSurat->isi_template, $nilai);
    }

    /*
     * Isi semua placeholder {{nama}} pada template.
     * Data kop & Kepala Sekolah diambil dari menu Pengaturan Surat.
     */
    public function isiTemplate(string $html, array $nilai): string
    {
        // Template yang tersimpan sebagai entity (&lt;div&gt;) dikembalikan ke HTML
        if (! str_contains($html, '<') && str_contains($html, '&lt;')) {
            $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $pengaturan = PengaturanSurat::ambil();

        if (! empty($nilai['tanggal_surat'])) {
            $nilai['tanggal_surat'] = Carbon::parse($nilai['tanggal_surat'])
                ->locale('id')
                ->translatedFormat('j F Y');
        }

        $nilai = array_merge($nilai, $pengaturan->placeholder());

        return preg_replace_callback(
            '/\{\{\s*\*?\s*([A-Za-z0-9_]+)\s*\*?\s*\}\}/',
            function ($m) use ($nilai) {
                // Kop surat sudah berupa HTML, tidak di-escape
                if ($m[1] === 'kop_surat') {
                    return $nilai['kop_surat'];
                }

                return $this->formatNilai($m[1], trim((string) ($nilai[$m[1]] ?? '')));
            },
            $html
        );
    }

    /*
     * Ubah isian form menjadi HTML surat.
     * Isian daftar diketik SATU BARIS PER ITEM, dan formatnya ditentukan
     * dari akhiran nama field:
     *
     *   ..._daftar   1. 2. 3.          (kolom tambahan dipisah " | ")
     *   ..._abjad    a. b. c.
     *   ..._poin     - - -
     *   ..._diktum   KESATU : / KEDUA : / ...
     *   ..._daftarpegawai  nama | pangkat/golongan | NIP | jabatan  -> blok bernomor
     *   ..._daftarsiswa    Nama | Kelas  -> tabel No, Nama, Kelas
     *   tembusan_daftar  -> "Tembusan:" + daftar (hilang kalau kosong)
     *   isi_surat / isi_penutup -> paragraf
     */
    public function formatNilai(string $key, string $teks): string
    {
        $baris = array_values(array_filter(
            array_map('trim', preg_split('/\R/', $teks)),
            fn ($b) => $b !== ''
        ));

        $sel = 'padding:0 0 2px; vertical-align:top;';

        if ($key === 'tembusan_daftar') {
            if (! $baris) {
                return '';
            }

            return '<div style="margin-top:14px;">Tembusan:</div>'
                . $this->formatNilai('tembusan_isi_daftar', $teks);
        }

        if (in_array($key, ['isi_surat', 'isi_penutup'], true) && $baris) {
            return implode('', array_map(
                fn ($p) => '<p style="margin:0 0 8px; text-align:justify;">' . e($p) . '</p>',
                $baris
            ));
        }

        $jenis = null;
        foreach (['daftar', 'abjad', 'poin', 'diktum', 'daftarpegawai', 'daftarsiswa'] as $akhiran) {
            if (str_ends_with($key, '_' . $akhiran)) {
                $jenis = $akhiran;
            }
        }

        if (! $jenis) {
            return nl2br(e($teks), false);
        }

        if (! $baris) {
            return '';
        }

        if ($jenis === 'daftarsiswa') {
            $html = '<table style="border-collapse:collapse; margin:4px 0 8px;">'
                . '<tr><td style="width:30px; ' . $sel . '">No</td><td style="width:300px; ' . $sel . '">Nama</td><td style="' . $sel . '">Kelas</td></tr>';

            foreach ($baris as $i => $b) {
                $kolom = array_map('trim', explode('|', $b));
                $html .= '<tr><td style="' . $sel . '">' . ($i + 1) . '.</td>'
                    . '<td style="' . $sel . '">' . e($kolom[0] ?? '') . '</td>'
                    . '<td style="' . $sel . '">' . e($kolom[1] ?? '') . '</td></tr>';
            }

            return $html . '</table>';
        }

        if ($jenis === 'daftarpegawai') {
            $label = ['nama', 'pangkat/golongan', 'NIP', 'jabatan'];
            $html = '<table style="border-collapse:collapse; width:100%;">';

            foreach ($baris as $i => $b) {
                $kolom = array_map('trim', explode('|', $b));

                foreach ($label as $j => $l) {
                    $html .= '<tr>'
                        . '<td style="width:22px; ' . $sel . '">' . ($j === 0 ? ($i + 1) . '.' : '') . '</td>'
                        . '<td style="width:135px; ' . $sel . '">' . $l . '</td>'
                        . '<td style="width:12px; ' . $sel . '">:</td>'
                        . '<td style="' . $sel . '">' . e($kolom[$j] ?? '-') . '</td></tr>';
                }

                $html .= '<tr><td colspan="4" style="height:6px;"></td></tr>';
            }

            return $html . '</table>';
        }

        if ($jenis === 'diktum') {
            $urutan = ['KESATU', 'KEDUA', 'KETIGA', 'KEEMPAT', 'KELIMA', 'KEENAM',
                'KETUJUH', 'KEDELAPAN', 'KESEMBILAN', 'KESEPULUH'];
            $html = '<table style="border-collapse:collapse; width:100%;">';

            foreach ($baris as $i => $b) {
                $html .= '<tr><td style="width:120px; ' . $sel . '">' . ($urutan[$i] ?? 'KE-' . ($i + 1)) . '</td>'
                    . '<td style="width:15px; ' . $sel . '">:</td>'
                    . '<td style="text-align:justify; ' . $sel . ' padding-bottom:4px;">' . e($b) . '</td></tr>';
            }

            return $html . '</table>';
        }

        // daftar / abjad / poin
        $html = '<table style="border-collapse:collapse; width:100%;">';

        foreach ($baris as $i => $b) {
            $penanda = match ($jenis) {
                'abjad' => chr(97 + ($i % 26)) . '.',
                'poin'  => '-',
                default => ($i + 1) . '.',
            };

            $kolom = array_map('trim', explode('|', $b));
            $isi = '<td style="text-align:justify; ' . $sel . '">' . e(array_shift($kolom)) . '</td>';

            foreach ($kolom as $k) {
                $isi .= '<td style="' . $sel . ' padding-left:10px; white-space:nowrap;">' . e($k) . '</td>';
            }

            $html .= '<tr><td style="width:22px; ' . $sel . '">' . $penanda . '</td>' . $isi . '</tr>';
        }

        return $html . '</table>';
    }

    /*
     * Ubah <img src="..."> yang menunjuk ke /images/... atau /storage/...
     * (alamat relatif maupun lengkap http://...) menjadi base64.
     */
    public function sematkanGambar(string $html): string
    {
        return preg_replace_callback(
            '/(<img\b[^>]*?\bsrc\s*=\s*)(["\'])(.*?)\2/i',
            function ($m) {
                $src = html_entity_decode($m[3]);

                if (str_starts_with($src, 'data:')) {
                    return $m[0];
                }

                $path = urldecode((string) parse_url($src, PHP_URL_PATH));
                $file = null;

                if (preg_match('#/?images/(.+)$#', $path, $x)) {
                    $file = public_path('images/' . $x[1]);
                } elseif (preg_match('#/?storage/(.+)$#', $path, $x)) {
                    $file = storage_path('app/public/' . $x[1]);
                }

                if (! $file || ! is_file($file)) {
                    return $m[0];
                }

                $mime = mime_content_type($file) ?: 'image/png';

                return $m[1] . $m[2] . 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file)) . $m[2];
            },
            $html
        );
    }
}

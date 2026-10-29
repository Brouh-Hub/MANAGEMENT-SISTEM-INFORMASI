<?php

namespace Database\Seeders;

use App\Models\TemplateSurat;
use App\Models\TemplateSuratField;
use App\Models\User;
use Illuminate\Database\Seeder;

/*
| Template surat keluar SMK Negeri 4 Tanjungpinang.
| Kop surat & data Kepala Sekolah TIDAK ditulis di sini: diatur TU di menu
| "Pengaturan Surat" dan masuk lewat placeholder {{kop_surat}}.
|
|   1. Surat Dinas Standar
|   2. Surat Undangan
|   3. Surat Keputusan
|   4. Surat Perintah Tugas
|   5. Surat Rekomendasi Mutasi Siswa
|
| Jalankan: php artisan db:seed --class=TemplateSuratStandarSeeder
|
| Aman dijalankan ulang: template & field dengan nama/key yang sama diperbarui,
| template lain tidak disentuh.
|
| Isian daftar diketik satu baris per item. Format otomatis dari akhiran key:
|   _daftar (1. 2.), _abjad (a. b.), _poin (-), _diktum (KESATU, KEDUA),
|   _daftarpegawai (Nama | Pangkat | NIP | Jabatan), _daftarsiswa (Nama | Kelas)
*/
class TemplateSuratStandarSeeder extends Seeder
{
    public function run(): void
    {
        $this->simpan(
            'Surat Dinas Standar',
            'Surat Dinas',
            <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; font-size:11pt; line-height:1.25;">
{{kop_surat}}

<table style="width:100%; border-collapse:collapse; margin-bottom:24px;">
    <tr>
        <td style="vertical-align:top;">
            <table style="border-collapse:collapse;">
                <tr><td style="width:75px;">Nomor</td><td style="width:12px;">:</td><td>{{nomor_surat}}</td></tr>
                <tr><td>Sifat</td><td>:</td><td>{{sifat}}</td></tr>
                <tr><td>Lampiran</td><td>:</td><td>{{lampiran}}</td></tr>
                <tr><td style="vertical-align:top;">Hal</td><td style="vertical-align:top;">:</td><td>{{perihal}}</td></tr>
            </table>
        </td>
        <td style="width:40%; vertical-align:top;">
            {{kota}}, {{tanggal_surat}}<br>
            Kepada :<br>
            Yth. {{tujuan}}<br>
            di –<br>
            <span style="padding-left:20px;">{{alamat_tujuan}}</span>
        </td>
    </tr>
</table>

<div style="text-align:justify;">{{isi_surat}}</div>

<table style="width:100%; border-collapse:collapse; margin-top:14px; page-break-inside:avoid;">
    <tr>
        <td style="width:52%;"></td>
        <td style="width:48%;">
            Kepala Sekolah,
            <div style="height:52px;"></div>
            {{nama_kepala_sekolah}}<br>
            {{pangkat_kepala_sekolah}}<br>
            NIP {{nip_kepala_sekolah}}
        </td>
    </tr>
</table>
{{tembusan_daftar}}
</div>
HTML,
            [
                ['key' => 'tembusan_daftar', 'label' => 'Tembusan (opsional, satu baris satu tujuan)', 'type' => 'textarea', 'is_required' => false, 'default_value' => '']
            ]
        );

        $this->simpan(
            'Surat Undangan',
            'Surat Undangan',
            <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; font-size:11pt; line-height:1.25;">
{{kop_surat}}

<table style="width:100%; border-collapse:collapse; margin-bottom:24px;">
    <tr>
        <td style="vertical-align:top;">
            <table style="border-collapse:collapse;">
                <tr><td style="width:75px;">Nomor</td><td style="width:12px;">:</td><td>{{nomor_surat}}</td></tr>
                <tr><td>Sifat</td><td>:</td><td>{{sifat}}</td></tr>
                <tr><td>Lampiran</td><td>:</td><td>{{lampiran}}</td></tr>
                <tr><td style="vertical-align:top;">Hal</td><td style="vertical-align:top;">:</td><td>{{perihal}}</td></tr>
            </table>
        </td>
        <td style="width:40%; vertical-align:top; text-align:right;">
            {{kota}}, {{tanggal_surat}}
        </td>
    </tr>
</table>

<div style="margin:-8px 0 24px;">
    Yth. {{tujuan}}<br>
    di<br>
    {{alamat_tujuan}}
</div>

<div style="text-align:justify; text-indent:1.2cm;">{{isi_surat}}</div>
<table style="border-collapse:collapse; margin:4px 0 16px 1.2cm;">
    <tr><td style="width:105px;">Hari, tanggal</td><td style="width:15px;">:</td><td>{{hari_tanggal_acara}}</td></tr>
    <tr><td>Pukul</td><td>:</td><td>{{pukul}}</td></tr>
    <tr><td>Tempat</td><td>:</td><td>{{tempat}}</td></tr>
</table>
<div style="text-align:justify;">{{isi_penutup}}</div>

<table style="width:100%; border-collapse:collapse; margin-top:14px; page-break-inside:avoid;">
    <tr>
        <td style="width:52%;"></td>
        <td style="width:48%;">
            Kepala Sekolah,
            <div style="height:52px;"></div>
            {{nama_kepala_sekolah}}<br>
            {{pangkat_kepala_sekolah}}<br>
            NIP {{nip_kepala_sekolah}}
        </td>
    </tr>
</table>
{{tembusan_daftar}}
</div>
HTML,
            [
                ['key' => 'tembusan_daftar', 'label' => 'Tembusan (opsional, satu baris satu tujuan)', 'type' => 'textarea', 'is_required' => false, 'default_value' => '']
            ]
        );

        $this->simpan(
            'Surat Keputusan',
            'Surat Keputusan',
            <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; font-size:10.5pt; line-height:1.2;">
{{kop_surat}}

<div style="text-align:center; line-height:1.35;">
    SURAT KEPUTUSAN<br>
    KEPALA SEKOLAH MENENGAH KEJURUAN NEGERI 4 TANJUNGPINANG<br>
    NOMOR {{nomor_surat}}<br>
    <div style="height:6px;"></div>
    TENTANG<br>
    <span style="text-transform:uppercase;">{{perihal}}</span><br>
    <div style="height:6px;"></div>
    KEPALA SMK NEGERI 4 TANJUNGPINANG,
</div>

<table style="width:100%; border-collapse:collapse; margin-top:8px;">
    <tr>
        <td style="width:110px; vertical-align:top;">Menimbang</td>
        <td style="width:15px; vertical-align:top;">:</td>
        <td style="vertical-align:top;">{{menimbang_abjad}}</td>
    </tr>
    <tr><td colspan="3" style="height:4px;"></td></tr>
    <tr>
        <td style="vertical-align:top;">Mengingat</td>
        <td style="vertical-align:top;">:</td>
        <td style="vertical-align:top;">{{mengingat_daftar}}</td>
    </tr>
</table>

<div style="text-align:center; margin:6px 0 4px;">MEMUTUSKAN</div>

<table style="width:100%; border-collapse:collapse;">
    <tr><td style="width:120px;">Menetapkan</td><td style="width:15px;">:</td><td></td></tr>
</table>
{{keputusan_diktum}}

<table style="width:100%; border-collapse:collapse; margin-top:14px; page-break-inside:avoid;">
    <tr>
        <td style="width:52%;"></td>
        <td style="width:48%;">
            <table style="border-collapse:collapse;"><tr><td style="width:105px;">Ditetapkan di</td><td style="width:12px;">:</td><td>{{kota}}</td></tr><tr><td>Pada tanggal</td><td>:</td><td>{{tanggal_surat}}</td></tr></table>
            Kepala Sekolah,
            <div style="height:52px;"></div>
            {{nama_kepala_sekolah}}<br>
            {{pangkat_kepala_sekolah}}<br>
            NIP {{nip_kepala_sekolah}}
        </td>
    </tr>
</table>
{{tembusan_daftar}}
</div>
HTML,
            [
                ['key' => 'menimbang_abjad', 'label' => 'Menimbang (satu baris satu poin, otomatis jadi a, b, c)', 'type' => 'textarea', 'is_required' => true, 'default_value' => ''],
                ['key' => 'mengingat_daftar', 'label' => 'Mengingat (satu baris satu dasar hukum, otomatis bernomor)', 'type' => 'textarea', 'is_required' => true, 'default_value' => 'Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;
Undang-Undang Nomor 14 Tahun 2005 tentang Guru dan Dosen;
Peraturan Pemerintah Nomor 17 Tahun 2010 tentang Pengelolaan dan Penyelenggaraan Pendidikan;
Peraturan Pemerintah Nomor 4 Tahun 2022 tentang Perubahan Peraturan Pemerintah Nomor 57 Tahun 2021 tentang Standar Nasional Pendidikan;
Peraturan Menteri PAN dan Reformasi Birokrasi No. 16 Tahun 2009 tentang Jabatan Fungsional Guru dan Angka Kreditnya;
Permendikbud RI No. 53 Tahun 2018 tentang Pemenuhan Beban Kerja Guru, Kepala Sekolah dan Pengawas Sekolah;
Peraturan Menteri PAN dan Reformasi Birokrasi No. 21 Tahun 2024 tentang Jabatan Fungsional Guru.'],
                ['key' => 'keputusan_diktum', 'label' => 'Isi Keputusan (satu baris satu diktum, otomatis jadi KESATU, KEDUA, ...)', 'type' => 'textarea', 'is_required' => true, 'default_value' => 'Isi keputusan pokok, seperti tersebut pada lampiran Surat Keputusan ini;
Segala biaya yang timbul akibat pelaksanaan keputusan ini dibebankan pada anggaran yang sesuai;
Apabila terdapat kekeliruan dalam keputusan ini, akan dibetulkan sebagaimana mestinya;
Keputusan ini berlaku sejak tanggal ditetapkan.'],
                ['key' => 'tembusan_daftar', 'label' => 'Tembusan (opsional, satu baris satu tujuan)', 'type' => 'textarea', 'is_required' => false, 'default_value' => 'Kepala Dinas Pendidikan Provinsi Kepulauan Riau
Yang bersangkutan']
            ]
        );

        $this->simpan(
            'Surat Perintah Tugas',
            'Surat Tugas',
            <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; font-size:11pt; line-height:1.25;">
{{kop_surat}}

<div style="text-align:center; line-height:1.35; margin-bottom:14px;">
    SURAT PERINTAH TUGAS<br>
    NOMOR : {{nomor_surat}}
</div>

<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:110px; vertical-align:top;">Dasar</td>
        <td style="width:15px; vertical-align:top;">:</td>
        <td style="text-align:justify; vertical-align:top;">{{dasar}}</td>
    </tr>
</table>

<div style="text-align:center; margin:14px 0 10px;">MEMERINTAHKAN :</div>

<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:110px; vertical-align:top;">Kepada</td>
        <td style="width:15px; vertical-align:top;">:</td>
        <td style="vertical-align:top;">{{petugas_daftarpegawai}}</td>
    </tr>
    <tr>
        <td style="vertical-align:top;">Untuk</td>
        <td style="vertical-align:top;">:</td>
        <td style="text-align:justify; vertical-align:top;">{{untuk}}{{peserta_daftarsiswa}}</td>
    </tr>
</table>

<p style="margin-top:12px;">Tugas ini harap dilaksanakan dengan penuh tanggung jawab dan setelah selesai membuat laporan.</p>

<table style="width:100%; border-collapse:collapse; margin-top:14px; page-break-inside:avoid;">
    <tr>
        <td style="width:52%;"></td>
        <td style="width:48%;">
            <table style="border-collapse:collapse;"><tr><td style="width:105px;">Ditetapkan di</td><td style="width:12px;">:</td><td>{{kota}}</td></tr><tr><td>Pada tanggal</td><td>:</td><td>{{tanggal_surat}}</td></tr></table>
            Kepala Sekolah,
            <div style="height:52px;"></div>
            {{nama_kepala_sekolah}}<br>
            {{pangkat_kepala_sekolah}}<br>
            NIP {{nip_kepala_sekolah}}
        </td>
    </tr>
</table>
{{tembusan_daftar}}
</div>
HTML,
            [
                ['key' => 'dasar', 'label' => 'Dasar (surat/undangan yang menjadi dasar tugas)', 'type' => 'textarea', 'is_required' => true, 'default_value' => ''],
                ['key' => 'petugas_daftarpegawai', 'label' => 'Pegawai yang ditugaskan (satu baris per orang: Nama | Pangkat/Golongan | NIP | Jabatan)', 'type' => 'textarea', 'is_required' => true, 'default_value' => ''],
                ['key' => 'untuk', 'label' => 'Untuk (uraian tugas, tanggal, dan tempat)', 'type' => 'textarea', 'is_required' => true, 'default_value' => ''],
                ['key' => 'peserta_daftarsiswa', 'label' => 'Siswa yang didampingi (opsional, satu baris per siswa: Nama | Kelas)', 'type' => 'textarea', 'is_required' => false, 'default_value' => ''],
                ['key' => 'tembusan_daftar', 'label' => 'Tembusan (opsional, satu baris satu tujuan)', 'type' => 'textarea', 'is_required' => false, 'default_value' => '']
            ]
        );

        $this->simpan(
            'Surat Rekomendasi Mutasi Siswa',
            'Surat Rekomendasi',
            <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; font-size:11pt; line-height:1.25;">
{{kop_surat}}

<div style="text-align:center; line-height:1.35; margin-bottom:14px;">
    SURAT REKOMENDASI MUTASI/PINDAH SISWA<br>
    Nomor: {{nomor_surat}}
</div>

<p style="margin-bottom:4px;">Yang bertanda tangan di bawah ini</p>
<table style="border-collapse:collapse; margin-left:0.8cm;">
<tr><td style="width:160px; vertical-align:top;">nama</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{nama_kepala_sekolah}}</td></tr><tr><td style="width:160px; vertical-align:top;">NIP</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{nip_kepala_sekolah}}</td></tr><tr><td style="width:160px; vertical-align:top;">pangkat/gol.</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{pangkat_kepala_sekolah}}/{{golongan_kepala_sekolah}}</td></tr><tr><td style="width:160px; vertical-align:top;">jabatan</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{jabatan_kepala_sekolah}}</td></tr>
</table>

<p style="text-align:justify; margin:10px 0 4px;">
    Berdasarkan pengajuan permohonan mutasi/pindah sekolah dari {{sekolah_asal}} ke {{sekolah_tujuan}}
    dari orang tua/wali murid atas nama
</p>
<table style="border-collapse:collapse; margin-left:0.8cm;">
<tr><td style="width:160px; vertical-align:top;">nama siswa</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{nama_siswa}}</td></tr><tr><td style="width:160px; vertical-align:top;">sekolah asal</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{sekolah_asal}}</td></tr><tr><td style="width:160px; vertical-align:top;">NISN</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{nisn}}</td></tr><tr><td style="width:160px; vertical-align:top;">kelas/semester/T.P.</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{kelas_semester_tp}}</td></tr><tr><td style="width:160px; vertical-align:top;">alamat sekolah asal</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{alamat_sekolah_asal}}</td></tr><tr><td style="width:160px; vertical-align:top;">tempat, tgl. lahir</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{ttl_siswa}}</td></tr><tr><td colspan="3">nama orangtua</td></tr><tr><td style="width:160px; vertical-align:top;">ayah</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{nama_ayah}}</td></tr><tr><td style="width:160px; vertical-align:top;">pekerjaan</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{pekerjaan_ayah}}</td></tr><tr><td style="width:160px; vertical-align:top;">ibu</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{nama_ibu}}</td></tr><tr><td style="width:160px; vertical-align:top;">pekerjaan</td><td style="width:15px; vertical-align:top;">:</td><td style="vertical-align:top;">{{pekerjaan_ibu}}</td></tr>
</table>

<p style="text-align:justify; margin:10px 0 4px;">
    Setelah melihat dan menverifikasi dokumen akademik serta alasan tujuan mutasi/pindah sekolah dengan ini
    kami merekomendasikan siswa yang bersangkutan mutasi/pindah ke {{sekolah_tujuan}} dengan melengkapi
</p>
<div style="margin-left:0.8cm;">{{kelengkapan_poin}}</div>
<p style="text-align:justify; margin-top:6px;">
    dan hal lainnya sehubungan dengan kepindahan siswa yang bersangkutan kami serahkan ke dinas terkait.
</p>
<p>Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.</p>

<table style="width:100%; border-collapse:collapse; margin-top:14px; page-break-inside:avoid;">
    <tr>
        <td style="width:52%;"></td>
        <td style="width:48%;">
            {{kota}}, {{tanggal_surat}}<br>
            Kepala Sekolah,
            <div style="height:52px;"></div>
            {{nama_kepala_sekolah}}<br>
            {{pangkat_kepala_sekolah}}<br>
            NIP {{nip_kepala_sekolah}}
        </td>
    </tr>
</table>

</div>
HTML,
            [
                ['key' => 'nama_siswa', 'label' => 'Nama siswa', 'type' => 'text', 'is_required' => true, 'default_value' => ''],
                ['key' => 'nisn', 'label' => 'NISN', 'type' => 'text', 'is_required' => true, 'default_value' => ''],
                ['key' => 'ttl_siswa', 'label' => 'Tempat, tanggal lahir', 'type' => 'text', 'is_required' => true, 'default_value' => ''],
                ['key' => 'kelas_semester_tp', 'label' => 'Kelas/Semester/T.P. (contoh: X/Ganjil/2026/2027)', 'type' => 'text', 'is_required' => true, 'default_value' => ''],
                ['key' => 'sekolah_asal', 'label' => 'Sekolah asal', 'type' => 'text', 'is_required' => true, 'default_value' => ''],
                ['key' => 'alamat_sekolah_asal', 'label' => 'Alamat sekolah asal', 'type' => 'textarea', 'is_required' => true, 'default_value' => ''],
                ['key' => 'sekolah_tujuan', 'label' => 'Sekolah tujuan', 'type' => 'text', 'is_required' => true, 'default_value' => 'SMK Negeri 4 Tanjungpinang'],
                ['key' => 'nama_ayah', 'label' => 'Nama ayah', 'type' => 'text', 'is_required' => false, 'default_value' => ''],
                ['key' => 'pekerjaan_ayah', 'label' => 'Pekerjaan ayah', 'type' => 'text', 'is_required' => false, 'default_value' => ''],
                ['key' => 'nama_ibu', 'label' => 'Nama ibu', 'type' => 'text', 'is_required' => false, 'default_value' => ''],
                ['key' => 'pekerjaan_ibu', 'label' => 'Pekerjaan ibu', 'type' => 'text', 'is_required' => false, 'default_value' => ''],
                ['key' => 'kelengkapan_poin', 'label' => 'Berkas yang harus dilengkapi (satu baris satu berkas)', 'type' => 'textarea', 'is_required' => true, 'default_value' => 'Surat pindah DAPODIK
Fotokopi Ijazah SLTP
Fotokopi Kartu Keluarga
Fotokopi Akte Kelahiran']
            ]
        );

        $this->command?->info('5 template surat siap dipakai.');
    }

    private function simpan(string $nama, string $jenis, string $isi, array $fields): void
    {
        preg_match_all('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', $isi, $m);

        $template = TemplateSurat::updateOrCreate(
            ['nama_template' => $nama],
            [
                'jenis_surat'     => $jenis,
                'isi_template'    => $isi,
                'variabel'        => array_values(array_unique($m[1])),
                'is_active'       => true,
                'user_id_pembuat' => User::where('jabatan', 'Tata Usaha')->value('id'),
            ]
        );

        foreach ($fields as $urutan => $field) {
            TemplateSuratField::updateOrCreate(
                ['template_surat_id' => $template->id, 'key' => $field['key']],
                $field + ['urutan' => $urutan + 1, 'is_active' => true]
            );
        }
    }
}

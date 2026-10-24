<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSurat extends Model
{
    protected $table = 'pengaturan_surat';

    protected $fillable = [
        'kop_baris_1', 'kop_baris_2', 'kop_baris_3',
        'kop_alamat', 'kop_telepon', 'kop_laman', 'kop_email',
        'logo_kiri', 'logo_kanan', 'tinggi_logo',
        'kota', 'nama_sekolah',
        'kepsek_nama', 'kepsek_pangkat', 'kepsek_golongan', 'kepsek_nip',
    ];

    // Logo bawaan (di folder public/images) kalau belum diganti
    public const LOGO_KIRI_BAWAAN  = '/images/logo-kepri.png';
    public const LOGO_KANAN_BAWAAN = '/images/logo-smkn4-tik.png';

    /*
     * Nilai awal, diambil dari config/surat.php supaya tetap sama
     * dengan yang sudah dipakai sebelumnya.
     */
    public static function bawaan(): array
    {
        return [
            'kop_baris_1'     => 'PEMERINTAH PROVINSI KEPULAUAN RIAU',
            'kop_baris_2'     => 'DINAS PENDIDIKAN',
            'kop_baris_3'     => 'SMK NEGERI 4 TANJUNGPINANG',
            'kop_alamat'      => 'Jalan Nusantara Km. 14, Batu IX, Tanjungpinang, Kepulauan Riau 29125',
            'kop_telepon'     => '(0771) 4440844',
            'kop_laman'       => 'smkn4tpi.sch.id',
            'kop_email'       => 'smkntpi4@gmail.com',
            'tinggi_logo'     => 78,
            'kota'            => config('surat.kota', 'Tanjungpinang'),
            'nama_sekolah'    => config('surat.nama_sekolah', 'SMK Negeri 4 Tanjungpinang'),
            'kepsek_nama'     => config('surat.kepala_sekolah.nama'),
            'kepsek_pangkat'  => config('surat.kepala_sekolah.pangkat'),
            'kepsek_golongan' => config('surat.kepala_sekolah.golongan'),
            'kepsek_nip'      => config('surat.kepala_sekolah.nip'),
        ];
    }

    // Ambil pengaturan (dibuat otomatis dengan nilai awal kalau belum ada)
    public static function ambil(): self
    {
        return static::query()->first() ?? static::create(static::bawaan());
    }

    public function logoKiriUrl(): string
    {
        return $this->logo_kiri ? '/storage/' . $this->logo_kiri : self::LOGO_KIRI_BAWAAN;
    }

    public function logoKananUrl(): string
    {
        return $this->logo_kanan ? '/storage/' . $this->logo_kanan : self::LOGO_KANAN_BAWAAN;
    }

    // Nilai placeholder umum untuk template surat
    public function placeholder(): array
    {
        return [
            'kop_surat'               => $this->kopHtml(),
            'kota'                    => $this->kota,
            'nama_sekolah'            => $this->nama_sekolah,
            'nama_kepala_sekolah'     => $this->kepsek_nama,
            'pangkat_kepala_sekolah'  => $this->kepsek_pangkat,
            'golongan_kepala_sekolah' => $this->kepsek_golongan,
            'nip_kepala_sekolah'      => $this->kepsek_nip,
            'jabatan_kepala_sekolah'  => 'Kepala ' . $this->nama_sekolah,
        ];
    }

    /*
     * HTML kop surat. Dipakai di PDF, di preview form surat keluar,
     * dan di halaman Pengaturan Surat (lewat partial yang sama).
     */
    public function kopHtml(): string
    {
        return view('admin.pengaturan-surat.kop', ['p' => $this])->render();
    }
}

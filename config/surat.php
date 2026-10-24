<?php

/*
|--------------------------------------------------------------------------
| Pengaturan Surat Keluar
|--------------------------------------------------------------------------
|
| Data ini otomatis mengisi placeholder di template surat:
|   {{kota}}, {{nama_sekolah}}, {{nama_kepala_sekolah}},
|   {{pangkat_kepala_sekolah}}, {{golongan_kepala_sekolah}},
|   {{nip_kepala_sekolah}}, {{jabatan_kepala_sekolah}}
|
| Setelah mengubah file ini, jalankan: php artisan config:clear
|
*/

return [

    'kota' => 'Tanjungpinang',

    'nama_sekolah' => 'SMK Negeri 4 Tanjungpinang',

    'kepala_sekolah' => [
        'nama'    => 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.',
        'pangkat'  => 'Pembina Utama Muda',
        'golongan' => 'IV/c',
        'nip'     => '19770421 200502 2 011',
    ],

];

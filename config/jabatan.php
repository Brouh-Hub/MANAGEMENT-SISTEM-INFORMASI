<?php

/*
| Master jabatan pengguna.
| Hak akses sistem dibaca dari nama jabatan, jadi jabatan dipilih dari daftar ini
| (bukan diketik bebas) supaya tidak ada salah ketik seperti "Kepala Tu".
|
| Jabatan yang punya menu/akses khusus:
|   Tata Usaha, Kepala Tata Usaha, Kepala Sekolah, Waka Humas
| Jabatan lain mendapat menu "Disposisi Saya".
|
| Kalau sekolah punya jabatan baru, cukup tambahkan di sini.
*/
return [
    'daftar' => [
        'Kepala Sekolah',
        'Kepala Tata Usaha',
        'Tata Usaha',
        'Waka Kurikulum',
        'Waka Kesiswaan',
        'Waka Sarana dan Prasarana',
        'Waka Humas',
        'Kepala Program Keahlian',
        'Pembina OSIS',
        'Bimbingan Konseling',
        'Guru',
    ],
];

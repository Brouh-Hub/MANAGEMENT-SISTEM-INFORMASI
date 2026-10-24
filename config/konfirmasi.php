<?php

/*
| Konfirmasi surat oleh Kepala Tata Usaha.
*/
return [

    // Nama jabatan persis seperti di Manajemen User
    'jabatan' => 'Kepala Tata Usaha',

    // Pilihan alasan cepat saat surat dikembalikan ke TU
    // (Kepala TU tetap bisa mengetik alasan sendiri)
    'alasan_kembali' => [
        'Data surat belum lengkap',
        'Nomor atau tanggal surat salah',
        'File surat tidak jelas / salah unggah',
        'Perihal atau tujuan surat tidak sesuai',
        'Isi surat perlu diperbaiki',
        'Surat ganda (sudah pernah dicatat)',
    ],
];

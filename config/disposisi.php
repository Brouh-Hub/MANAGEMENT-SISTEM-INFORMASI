<?php

/*
|--------------------------------------------------------------------------
| Pengaturan Disposisi
|--------------------------------------------------------------------------
|
| Daftar pilihan alasan cepat yang muncul saat Kepala Sekolah
| mengarsipkan surat masuk (cukup diketahui).
|
| Silakan ubah, tambah, atau hapus sesuai kebijakan sekolah.
| Setelah mengubah file ini, jalankan: php artisan config:clear
|
| Kepala Sekolah tetap bisa menulis alasan sendiri lewat pilihan "Lainnya…".
|
*/

return [

    // Pilihan alasan saat surat DIARSIPKAN / cukup diketahui (alasan opsional)
    'alasan_arsip' => [
        'Hanya pemberitahuan / edaran',
        'Sudah ditangani langsung',
        'Tidak memerlukan tindak lanjut',
    ],

    // Contoh instruksi yang muncul kalau Kepala Sekolah belum punya riwayat disposisi.
    // Setelah Kepsek mulai meneruskan surat, yang tampil adalah instruksi yang terakhir ia pakai.
    'instruksi_default' => [
        'Hadiri / wakili',
        'Tindak lanjuti dan laporkan hasilnya',
        'Koordinasikan dengan bagian terkait',
        'Siapkan surat balasan',
        'Siapkan data yang diminta',
        'Edarkan kepada guru dan staf',
    ],

    // Jumlah instruksi terakhir yang ditampilkan
    'jumlah_instruksi_terakhir' => 6,

];

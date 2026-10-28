<?php

use Illuminate\Support\Facades\Route;

// Login
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerkasController;

// Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LogLoginController;
use App\Http\Controllers\Admin\ProfilController;
use App\Http\Controllers\Admin\PengaturanSuratController;
use App\Http\Controllers\Admin\MitraController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\DokumenKerjasamaController;

// Persuratan
use App\Http\Controllers\Surat\SuratMasukController;
use App\Http\Controllers\Surat\SuratKeluarController;
use App\Http\Controllers\Surat\SuratKeluarFieldController;
use App\Http\Controllers\Surat\TemplateSuratController;
use App\Http\Controllers\Surat\DisposisiController;
use App\Http\Controllers\Surat\KonfirmasiSuratController;

// Sertifikat
use App\Http\Controllers\SertifikatController;
use App\Http\Controllers\TemplateSertifikatController;

/*
|==========================================================================
| HALAMAN AWAL
| Sistem ini tidak punya halaman publik: alamat utama langsung diarahkan
| ke login (atau ke dashboard kalau sudah login).
|==========================================================================
*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('admin.login');
})->name('home');


/*
|==========================================================================
| ADMIN  (semua alamat diawali /admin)
|==========================================================================
*/
Route::prefix('admin')->group(function () {

    /*
    |----------------------------------------------------------------------
    | LOGIN (tamu)
    |----------------------------------------------------------------------
    */
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:20,1')
        ->name('admin.login.submit');


    /*
    |----------------------------------------------------------------------
    | SEMUA HALAMAN DI BAWAH INI WAJIB LOGIN
    |----------------------------------------------------------------------
    */
    // TanpaCache: halaman admin tidak disimpan browser, jadi setelah logout
    // tombol Back tidak bisa menampilkan halaman lama (harus login ulang)
    Route::middleware(['auth', \App\Http\Middleware\TanpaCache::class])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Berkas surat (disk private) — hanya bisa dibuka setelah login
        Route::get('/berkas/{path}', [BerkasController::class, 'show'])
            ->where('path', '.+')
            ->name('berkas');

        // Profil — semua user yang login
        Route::get('/profil', [ProfilController::class, 'show'])->name('profil.show');
        Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
        Route::put('/profil/password', [ProfilController::class, 'password'])->name('profil.password');

        // Laporan rekap surat (cetak PDF)
        Route::get('/laporan/pdf', [DashboardController::class, 'laporanPdf'])
            ->middleware('jabatan:Kepala Sekolah,Tata Usaha,Kepala Tata Usaha')
            ->name('laporan.pdf');


        /*
        |------------------------------------------------------------------
        | DISPOSISI — semua user yang login (penerima disposisi)
        |------------------------------------------------------------------
        */
        Route::get('/disposisi-saya', [DisposisiController::class, 'myDisposisi'])
            ->name('disposisi.saya');
        Route::put('/disposisi-saya/{disposisi}', [DisposisiController::class, 'updateStatus'])
            ->name('disposisi.update-status');
        Route::post('/disposisi-saya/{disposisi}/baca', [DisposisiController::class, 'tandaiDibaca'])
            ->name('disposisi.baca');
        Route::get('/disposisi-notifikasi', [DisposisiController::class, 'notifikasi'])
            ->name('disposisi.notifikasi');
        Route::get('/disposisi/{suratMasuk}/detail', [DisposisiController::class, 'detailSurat'])
            ->name('disposisi.detail');


        /*
        |------------------------------------------------------------------
        | KEPALA SEKOLAH — disposisi & persetujuan surat keluar
        |------------------------------------------------------------------
        */
        Route::middleware('jabatan:Kepala Sekolah')->group(function () {
            Route::resource('disposisi', DisposisiController::class)
                ->only(['index', 'destroy']);

            Route::get('/disposisi/{suratMasuk}/riwayat', [DisposisiController::class, 'riwayat'])
                ->name('disposisi.riwayat');
            Route::post('/disposisi/{disposisi}/teruskan', [DisposisiController::class, 'teruskan'])
                ->name('disposisi.teruskan');
            // Keputusan Kepsek: arsipkan surat
            Route::post('/disposisi/{disposisi}/putuskan', [DisposisiController::class, 'putuskan'])
                ->name('disposisi.putuskan');
            // Hapus instruksi dari daftar "Terakhir Anda pakai"
            Route::post('/disposisi-instruksi/hapus', [DisposisiController::class, 'hapusInstruksi'])
                ->name('disposisi.instruksi.hapus');

            // Setujui surat keluar
            Route::post('/surat-keluar/{id}/approve', [SuratKeluarController::class, 'approve'])
                ->name('surat-keluar.approve');
        });


        /*
        |------------------------------------------------------------------
        | KEPALA TATA USAHA — konfirmasi surat masuk & keluar
        |------------------------------------------------------------------
        */
        Route::middleware('jabatan:Kepala Tata Usaha')->group(function () {
            Route::get('/konfirmasi-surat', [KonfirmasiSuratController::class, 'index'])
                ->name('konfirmasi-surat.index');
            Route::post('/konfirmasi-surat/{jenis}/{id}', [KonfirmasiSuratController::class, 'putuskan'])
                ->whereIn('jenis', ['masuk', 'keluar'])
                ->whereNumber('id')
                ->name('konfirmasi-surat.putuskan');
        });


        /*
        |------------------------------------------------------------------
        | SURAT KELUAR — lihat daftar & PDF
        | (TU, Kepala Sekolah, Kepala TU)
        |------------------------------------------------------------------
        */
        Route::get('/surat-keluar', [SuratKeluarController::class, 'index'])
            ->middleware('jabatan:Tata Usaha,Kepala Sekolah,Kepala Tata Usaha')
            ->name('surat-keluar.index');
        Route::get('/surat-keluar/{id}/preview', [SuratKeluarController::class, 'preview'])
            ->middleware('jabatan:Tata Usaha,Kepala Sekolah,Kepala Tata Usaha')
            ->name('surat-keluar.preview');
        Route::get('/surat-keluar/{id}/download', [SuratKeluarController::class, 'download'])
            ->middleware('jabatan:Tata Usaha,Kepala Sekolah,Kepala Tata Usaha')
            ->name('surat-keluar.download');


        /*
        |------------------------------------------------------------------
        | MoU / PKS — lihat daftar (TU, Waka Humas, Kepala Sekolah)
        |------------------------------------------------------------------
        */
        Route::get('/mou-pks', [DokumenKerjasamaController::class, 'index'])
            ->middleware('jabatan:Tata Usaha,Waka Humas,Kepala Sekolah')
            ->name('mou-pks.index');


        /*
        |------------------------------------------------------------------
        | TATA USAHA & KEPALA TATA USAHA — input surat masuk & surat keluar
        |------------------------------------------------------------------
        */
        Route::middleware('jabatan:Tata Usaha,Kepala Tata Usaha')->group(function () {

            // ---------- Surat Masuk ----------
            // Perkiraan No. Agenda berikutnya (form Tambah surat masuk)
            Route::get('/surat-masuk-agenda-berikutnya', [SuratMasukController::class, 'agendaBerikutnya'])
                ->name('surat-masuk.agenda-berikutnya');
            Route::resource('surat-masuk', SuratMasukController::class)
                ->except(['create', 'edit', 'show']);

            // ---------- Surat Keluar ----------
            // Generate nomor surat (TU memasukkan password sendiri)
            Route::post('/surat-keluar/generate-nomor', [SuratKeluarController::class, 'generateNomor'])
                ->name('surat-keluar.generate-nomor');
            Route::resource('surat-keluar', SuratKeluarController::class)
                ->except(['index', 'create', 'edit', 'show']);
            Route::post('/surat-keluar/{id}/generate-pdf', [SuratKeluarController::class, 'generatePdf'])
                ->name('surat-keluar.generate-pdf');
            // Ajukan ke Kepala Tata Usaha
            Route::post('/surat-keluar/{id}/ajukan', [SuratKeluarController::class, 'ajukan'])
                ->name('surat-keluar.ajukan');
        });


        /*
        |------------------------------------------------------------------
        | TATA USAHA — user, pengaturan, template surat
        |------------------------------------------------------------------
        */
        Route::middleware('jabatan:Tata Usaha')->group(function () {

            // ---------- Pengaturan kop surat, penanda tangan & penomoran ----------
            Route::get('/pengaturan-surat', [PengaturanSuratController::class, 'index'])
                ->name('pengaturan-surat.index');
            Route::put('/pengaturan-surat', [PengaturanSuratController::class, 'update'])
                ->name('pengaturan-surat.update');
            Route::get('/pengaturan-surat/contoh-pdf', [PengaturanSuratController::class, 'contohPdf'])
                ->name('pengaturan-surat.contoh-pdf');
            Route::put('/pengaturan-surat/penomoran', [PengaturanSuratController::class, 'simpanPenomoran'])
                ->name('pengaturan-surat.penomoran');

            // ---------- Manajemen User ----------
            Route::resource('users', UserController::class)
                ->except(['create', 'edit', 'show']);

            // ---------- Log Aktivitas Login ----------
            Route::get('/log-login', [LogLoginController::class, 'index'])->name('log-login.index');

            // ---------- Template Surat (dikelola dari halaman Surat Keluar) ----------
            Route::get('/template-surat', fn () => redirect()->route('surat-keluar.index'))
                ->name('template-surat.index');
            Route::post('/template-surat', [TemplateSuratController::class, 'store'])
                ->name('template-surat.store');
            Route::put('/template-surat/{id}', [TemplateSuratController::class, 'update'])
                ->name('template-surat.update');
            Route::delete('/template-surat/{id}', [TemplateSuratController::class, 'destroy'])
                ->name('template-surat.destroy');

            Route::get('/surat-keluar/template/create', [TemplateSuratController::class, 'create'])
                ->name('surat-keluar.template.create');
            Route::get('/surat-keluar/template/{id}/edit', [TemplateSuratController::class, 'edit'])
                ->name('surat-keluar.template.edit');
            Route::post('/surat-keluar/template', [TemplateSuratController::class, 'store'])
                ->name('surat-keluar.template.store');
            Route::put('/surat-keluar/template/{id}', [TemplateSuratController::class, 'update'])
                ->name('surat-keluar.template.update');
            Route::delete('/surat-keluar/template/{id}', [TemplateSuratController::class, 'destroy'])
                ->name('surat-keluar.template.destroy');

            // ---------- Atur field form surat keluar ----------
            Route::post('/surat-keluar-fields', [SuratKeluarFieldController::class, 'store']);
            Route::put('/surat-keluar-fields/{field}', [SuratKeluarFieldController::class, 'update']);
            Route::delete('/surat-keluar-fields/{field}', [SuratKeluarFieldController::class, 'destroy']);
        });


        /*
        |------------------------------------------------------------------
        | TATA USAHA & WAKA HUMAS — sertifikat PKL & mitra industri
        |------------------------------------------------------------------
        */
        Route::middleware('jabatan:Tata Usaha,Waka Humas')->group(function () {

            // Generate nomor dokumen MoU/PKS (urutan sama dengan surat keluar)
            Route::post('/mou-pks-generate-nomor', [SuratKeluarController::class, 'generateNomor'])
                ->name('mou-pks.generate-nomor');

            // ---------- Data siswa (import Excel) ----------
            Route::get('/sertifikat/siswa', [SiswaController::class, 'daftarSiswa'])
                ->name('sertifikat.siswa');
            Route::get('/sertifikat/siswa/template', [SiswaController::class, 'templateSiswa'])
                ->name('sertifikat.siswa.template');
            Route::get('/sertifikat/siswa/{id}/detail', [SiswaController::class, 'detailSiswa'])
                ->name('sertifikat.siswa.detail');
            Route::post('/sertifikat/siswa/import', [SiswaController::class, 'importSiswa'])
                ->name('sertifikat.siswa.import');
            Route::post('/sertifikat/siswa/clear', [SiswaController::class, 'clearSemuaSiswa'])
                ->name('sertifikat.siswa.clear');
            // Hapus satu siswa (beserta PKL & kerja sama/mitra hasil import yang jadi kosong)
            Route::post('/sertifikat/siswa/{id}/hapus', [SiswaController::class, 'hapusSiswa'])
                ->name('sertifikat.siswa.hapus');

            // ---------- Sertifikat ----------
            Route::resource('sertifikat', SertifikatController::class)
                ->except(['create', 'edit', 'show']);
            Route::post('/sertifikat/{id}/generate', [SertifikatController::class, 'generate'])
                ->name('sertifikat.generate');

            // ---------- Template sertifikat ----------
            Route::resource('template-sertifikat', TemplateSertifikatController::class)
                ->except(['create', 'show', 'edit']);
            Route::get('/template-sertifikat/{id}/posisi', [TemplateSertifikatController::class, 'posisi'])
                ->name('template-sertifikat.posisi');
            Route::post('/template-sertifikat/{id}/posisi', [TemplateSertifikatController::class, 'updatePosisi'])
                ->name('template-sertifikat.posisi.update');
            Route::post('/template-sertifikat/{id}/gambar', [TemplateSertifikatController::class, 'updateGambar'])
                ->name('template-sertifikat.gambar');

            // ---------- Mitra industri & peserta PKL ----------
            Route::resource('mitra', MitraController::class)
                ->except(['create', 'edit', 'show']);
            Route::get('/mitra/{id}/pkl', [MitraController::class, 'pesertaPkl'])
                ->name('mitra.pkl');
            Route::get('/mitra/{mitraId}/pkl/siswa', [MitraController::class, 'daftarSiswaPkl'])
                ->name('mitra.pkl.siswa');
            Route::post('/mitra/{mitraId}/pkl', [MitraController::class, 'tambahPesertaPkl'])
                ->name('mitra.pkl.store');
            Route::delete('/mitra/{mitraId}/pkl/{pklId}', [MitraController::class, 'hapusPesertaPkl'])
                ->name('mitra.pkl.destroy');

            // ---------- MoU / PKS: tambah, ubah, hapus ----------
            Route::resource('mou-pks', DokumenKerjasamaController::class)
                ->parameters(['mou-pks' => 'mou_pk'])
                ->only(['store', 'update', 'destroy']);
        });
    });
});

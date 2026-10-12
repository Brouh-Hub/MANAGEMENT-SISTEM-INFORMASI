<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\KerjasamaController;
// Public Controllers
use App\Http\Controllers\Admin\MitraController as AdminMitraController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\BeritaController;
use App\Http\Controllers\Public\KategoriBeritaController;

// Surat Controllers
use App\Http\Controllers\Surat\SuratMasukController;
use App\Http\Controllers\Surat\SuratKeluarController;
use App\Http\Controllers\Surat\SuratKeluarFieldController;
use App\Http\Controllers\Surat\TemplateSuratController;
use App\Http\Controllers\Surat\DisposisiController;

// Admin Controllers
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;

// Root Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SertifikatController;
use App\Http\Controllers\TemplateSertifikatController;




// TU mengajukan surat kepada Kepala Sekolah


Route::get(
    '/sertifikat/siswa',
    [SertifikatController::class, 'daftarSiswa']
)->name('sertifikat.siswa');

Route::get(
    '/sertifikat/siswa/{id}/detail',
    [SertifikatController::class, 'detailSiswa']
)->name('sertifikat.siswa.detail');

Route::get(
    '/sertifikat/siswa/template',
    [SertifikatController::class, 'templateSiswa']
)->name('sertifikat.siswa.template');

Route::post(
    '/sertifikat/siswa/import',
    [SertifikatController::class, 'importSiswa']
)->name('sertifikat.siswa.import');
// Public User Biasa routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang-sekolah', [AboutController::class, 'index'])->name('about');
Route::get('/berita', [BeritaController::class, 'index'])->name('berita.index');
Route::get('/berita/{berita}', [BeritaController::class, 'show'])->name('berita.show');

// Admin Atau Tu routes
Route::prefix('admin')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('admin.login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('admin.login.submit');

    Route::middleware('auth')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard');

            // ROUTE DISPOSISI — KHUSUS KEPALA SEKOLAH
Route::middleware('jabatan:Kepala Sekolah')->group(function () {
    Route::resource('disposisi', DisposisiController::class)
        ->except(['create', 'edit', 'show']);

    Route::get(
        '/disposisi/{suratMasuk}/riwayat',
        [DisposisiController::class, 'riwayat']
    )->name('disposisi.riwayat');
});


        // TEMPEL ROUTE AJUKAN DI SINI
        Route::post(
            '/surat-keluar/{id}/ajukan',
            [SuratKeluarController::class, 'ajukan']
        )
            ->middleware('jabatan:Tata Usaha')
            ->name('surat-keluar.ajukan');


        // KODE ROUTE LAIN TETAP LANJUT DI SINI

        // =========================================================
// HALAMAN SURAT KELUAR
// =========================================================

Route::get(
    '/surat-keluar',
    [SuratKeluarController::class, 'index']
)
    ->middleware('jabatan:Tata Usaha,Kepala Sekolah')
    ->name('surat-keluar.index');


// =========================================================
// PREVIEW PDF
// =========================================================

Route::get(
    '/surat-keluar/{id}/preview',
    [SuratKeluarController::class, 'preview']
)
    ->middleware('jabatan:Tata Usaha,Kepala Sekolah')
    ->name('surat-keluar.preview');


// =========================================================
// DOWNLOAD PDF
// =========================================================

Route::get(
    '/surat-keluar/{id}/download',
    [SuratKeluarController::class, 'download']
)
    ->middleware('jabatan:Tata Usaha,Kepala Sekolah')
    ->name('surat-keluar.download');


// =========================================================
// APPROVE SURAT KELUAR — KEPALA SEKOLAH
// =========================================================

Route::post(
    '/surat-keluar/{id}/approve',
    [SuratKeluarController::class, 'approve']
)
    ->middleware('jabatan:Kepala Sekolah')
    ->name('surat-keluar.approve');

        // Generate Sertifikat — hanya Tata Usaha dan Waka Humas
// Generate Sertifikat — hanya Tata Usaha dan Waka Humas
Route::middleware('jabatan:Tata Usaha,Waka Humas')->group(function () {

    // =========================================================
    // DATA SISWA
    // =========================================================

    Route::get(
        '/sertifikat/siswa',
        [SertifikatController::class, 'daftarSiswa']
    )->name('sertifikat.siswa');

    Route::get(
    '/sertifikat/siswa/{id}/detail',
    [SertifikatController::class, 'detailSiswa']
)->name('sertifikat.siswa.detail');


    Route::get(
        '/sertifikat/siswa/template',
        [SertifikatController::class, 'templateSiswa']
    )->name('sertifikat.siswa.template');


    Route::post(
        '/sertifikat/siswa/import',
        [SertifikatController::class, 'importSiswa']
    )->name('sertifikat.siswa.import');


    // =========================================================
    // CLEAR SEMUA DATA SISWA
    // =========================================================

    Route::post(
        '/sertifikat/siswa/clear',
        [SertifikatController::class, 'clearSemuaSiswa']
    )->name('sertifikat.siswa.clear');


    // =========================================================
    // SERTIFIKAT
    // =========================================================

    Route::resource('sertifikat', SertifikatController::class)
        ->except(['create', 'edit', 'show']);


    // =========================================================
    // GENERATE PDF
    // =========================================================

    Route::post(
        '/sertifikat/{id}/generate',
        [SertifikatController::class, 'generate']
    )->name('sertifikat.generate');

});

        // Tata Usaha — mitra, users, surat masuk & keluar, template sertifikat
        // (Kepala Sekolah tidak dapat akses ini)
// =========================================================
// MITRA INDUSTRI
// =========================================================
Route::middleware('jabatan:Tata Usaha,Waka Humas')->group(function () {

    // CRUD Mitra
    Route::resource('mitra', AdminMitraController::class)
        ->except(['create', 'edit', 'show']);

    // =====================================================
    // PESERTA PKL DARI HALAMAN MITRA
    // =====================================================

    // Ambil semua peserta PKL dari mitra
    Route::get(
        '/mitra/{id}/pkl',
        [AdminMitraController::class, 'pesertaPkl']
    )->name('mitra.pkl');

    // Ambil daftar siswa yang belum masuk ke kerja sama
    Route::get(
        '/mitra/{mitraId}/pkl/siswa',
        [AdminMitraController::class, 'daftarSiswaPkl']
    )->name('mitra.pkl.siswa');

    // Tambah peserta PKL
    Route::post(
        '/mitra/{mitraId}/pkl',
        [AdminMitraController::class, 'tambahPesertaPkl']
    )->name('mitra.pkl.store');

    // Hapus peserta PKL
    Route::delete(
        '/mitra/{mitraId}/pkl/{pklId}',
        [AdminMitraController::class, 'hapusPesertaPkl']
    )->name('mitra.pkl.destroy');


    // =====================================================
    // USER
    // =====================================================

    Route::resource('users', UserController::class)
        ->except(['create', 'edit', 'show']);


    // =====================================================
    // SURAT MASUK
    // =====================================================

    Route::resource('surat-masuk', SuratMasukController::class)
        ->except(['create', 'edit', 'show']);

    Route::post(
        '/surat-masuk/{id}/disposisikan',
        [SuratMasukController::class, 'disposisikan']
    )->name('surat-masuk.disposisikan');


    // =====================================================
    // SURAT KELUAR
    // =====================================================

    Route::get(
        '/surat-keluar/create',
        [SuratKeluarController::class, 'create']
    )->name('surat-keluar.create');

    Route::resource('surat-keluar', SuratKeluarController::class)
        ->except(['index', 'create', 'edit', 'show']);

    Route::post(
        '/surat-keluar/{id}/generate-pdf',
        [SuratKeluarController::class, 'generatePdf']
    )->name('surat-keluar.generate-pdf');


    // =====================================================
    // TEMPLATE SURAT
    // =====================================================

    Route::get('/template-surat', function () {
        return redirect()->route('surat-keluar.index');
    })->name('template-surat.index');

    Route::post(
        '/template-surat',
        [TemplateSuratController::class, 'store']
    )->name('template-surat.store');

    Route::put(
        '/template-surat/{id}',
        [TemplateSuratController::class, 'update']
    )->name('template-surat.update');

    Route::delete(
        '/template-surat/{id}',
        [TemplateSuratController::class, 'destroy']
    )->name('template-surat.destroy');


    // Editor Template Surat
    Route::get(
        '/surat-keluar/template/create',
        [TemplateSuratController::class, 'create']
    )->name('surat-keluar.template.create');

    Route::get(
        '/surat-keluar/template/{id}/edit',
        [TemplateSuratController::class, 'edit']
    )->name('surat-keluar.template.edit');

    Route::post(
        '/surat-keluar/template',
        [TemplateSuratController::class, 'store']
    )->name('surat-keluar.template.store');

    Route::put(
        '/surat-keluar/template/{id}',
        [TemplateSuratController::class, 'update']
    )->name('surat-keluar.template.update');

    Route::delete(
        '/surat-keluar/template/{id}',
        [TemplateSuratController::class, 'destroy']
    )->name('surat-keluar.template.destroy');


    // =====================================================
    // FIELD SURAT KELUAR
    // =====================================================

    Route::post(
        'surat-keluar-fields',
        [SuratKeluarFieldController::class, 'store']
    );

    Route::put(
        'surat-keluar-fields/{field}',
        [SuratKeluarFieldController::class, 'update']
    );

    Route::delete(
        'surat-keluar-fields/{field}',
        [SuratKeluarFieldController::class, 'destroy']
    );


    // =====================================================
    // TEMPLATE SERTIFIKAT
    // =====================================================

    Route::resource(
        'template-sertifikat',
        TemplateSertifikatController::class
    )->except(['create', 'show', 'edit']);

    Route::get(
        '/template-sertifikat/{id}/posisi',
        [TemplateSertifikatController::class, 'posisi']
    )->name('template-sertifikat.posisi');

    Route::post(
        '/template-sertifikat/{id}/posisi',
        [TemplateSertifikatController::class, 'updatePosisi']
    )->name('template-sertifikat.posisi.update');

    Route::post(
        '/template-sertifikat/{id}/gambar',
        [TemplateSertifikatController::class, 'updateGambar']
    )->name('template-sertifikat.gambar');

});
            Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);

            Route::resource('surat-masuk', SuratMasukController::class)->except(['create', 'edit', 'show']);
            Route::post('/surat-masuk/{id}/disposisikan', [SuratMasukController::class, 'disposisikan'])->name('surat-masuk.disposisikan');

            // Halaman Tambah Surat Keluar
            Route::get(
                '/surat-keluar/create',
                [SuratKeluarController::class, 'create']
            )->name('surat-keluar.create');

            // Surat keluar: simpan, ubah, hapus
            Route::resource('surat-keluar', SuratKeluarController::class)
                ->except(['index', 'create', 'edit', 'show']);

            // Generate PDF surat keluar dari template
            Route::post(
                '/surat-keluar/{id}/generate-pdf',
                [SuratKeluarController::class, 'generatePdf']
            )->name('surat-keluar.generate-pdf');

            // Template Surat dikelola langsung dari halaman Surat Keluar
            Route::get('/template-surat', function () {
                return redirect()->route('surat-keluar.index');
            })->name('template-surat.index');

            Route::post(
                '/template-surat',
                [TemplateSuratController::class, 'store']
            )->name('template-surat.store');

            Route::put(
                '/template-surat/{id}',
                [TemplateSuratController::class, 'update']
            )->name('template-surat.update');

            Route::delete(
                '/template-surat/{id}',
                [TemplateSuratController::class, 'destroy']
            )->name('template-surat.destroy');

            // Editor Template Surat di dalam modul Surat Keluar
            Route::get(
                '/surat-keluar/template/create',
                [TemplateSuratController::class, 'create']
            )->name('surat-keluar.template.create');

            Route::get(
                '/surat-keluar/template/{id}/edit',
                [TemplateSuratController::class, 'edit']
            )->name('surat-keluar.template.edit');

            Route::post(
                '/surat-keluar/template',
                [TemplateSuratController::class, 'store']
            )->name('surat-keluar.template.store');

            Route::put(
                '/surat-keluar/template/{id}',
                [TemplateSuratController::class, 'update']
            )->name('surat-keluar.template.update');

            Route::delete(
                '/surat-keluar/template/{id}',
                [TemplateSuratController::class, 'destroy']
            )->name('surat-keluar.template.destroy');

            // Atur field form surat keluar
            Route::post('surat-keluar-fields', [SuratKeluarFieldController::class, 'store']);
            Route::put('surat-keluar-fields/{field}', [SuratKeluarFieldController::class, 'update']);
            Route::delete('surat-keluar-fields/{field}', [SuratKeluarFieldController::class, 'destroy']);

            Route::resource('template-sertifikat', TemplateSertifikatController::class)->except(['create', 'show', 'edit']);
            Route::get('/template-sertifikat/{id}/posisi', [TemplateSertifikatController::class, 'posisi'])->name('template-sertifikat.posisi');
            Route::post('/template-sertifikat/{id}/posisi', [TemplateSertifikatController::class, 'updatePosisi'])->name('template-sertifikat.posisi.update');
            Route::post('/template-sertifikat/{id}/gambar', [TemplateSertifikatController::class, 'updateGambar'])->name('template-sertifikat.gambar');
        });

        Route::resource('berita', BeritaController::class)->except(['index', 'show', 'create', 'edit']);
        Route::resource('kategori-berita', KategoriBeritaController::class)->except(['create', 'edit', 'show']);

        // Setting bukan resource biasa — cuma index (tampil) & update (simpan)
        Route::get('/setting', [SettingController::class, 'index'])->name('admin.setting');
        Route::put('/setting', [SettingController::class, 'update'])->name('admin.setting.update');

        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    });

Route::middleware('jabatan:Tata Usaha,Waka Humas')->group(function () {

    // Data kerja sama
    Route::resource('kerjasama', KerjasamaController::class)
        ->except(['create', 'edit', 'show']);

    // Daftar peserta PKL pada suatu kerja sama
    Route::get(
        '/kerjasama/{id}/peserta',
        [KerjasamaController::class, 'peserta']
    )->name('kerjasama.peserta');

    // Tambah peserta PKL
    Route::post(
        '/kerjasama/{id}/peserta',
        [KerjasamaController::class, 'tambahPeserta']
    )->name('kerjasama.peserta.store');

    // Hapus peserta PKL
    Route::delete(
        '/kerjasama/{kerjasamaId}/peserta/{pklId}',
        [KerjasamaController::class, 'hapusPeserta']
    )->name('kerjasama.peserta.destroy');

    // Data siswa untuk dropdown peserta PKL
    Route::get(
        '/kerjasama-daftar-siswa',
        [KerjasamaController::class, 'daftarSiswa']
    )->name('kerjasama.daftar-siswa');

});


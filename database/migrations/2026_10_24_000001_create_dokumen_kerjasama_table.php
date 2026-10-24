<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Dokumen MoU / PKS dengan mitra industri (DUDI).
| Disimpan terpisah dari tabel "kerjasama" (yang dipakai untuk peserta PKL),
| supaya dokumen MoU tidak ikut terhapus saat data siswa dibersihkan.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_kerjasama', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industri_id')->constrained('industri')->cascadeOnDelete(); // mitra
            $table->enum('jenis', ['MoU', 'PKS', 'Lainnya'])->default('MoU');
            $table->string('nomor_dokumen');                    // nomor dari sekolah
            $table->string('nomor_dokumen_mitra')->nullable();  // nomor dari pihak mitra
            $table->string('judul');
            $table->text('ruang_lingkup')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir')->nullable();       // kosong = tidak ada batas waktu
            $table->string('penandatangan_sekolah')->nullable();
            $table->string('penandatangan_mitra')->nullable();
            $table->string('file_path')->nullable();            // berkas PDF/scan
            $table->text('keterangan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('nomor_dokumen');
            $table->index('tanggal_berakhir');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_kerjasama');
    }
};

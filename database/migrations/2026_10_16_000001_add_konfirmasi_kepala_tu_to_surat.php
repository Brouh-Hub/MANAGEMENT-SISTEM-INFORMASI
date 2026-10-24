<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| Konfirmasi Kepala Tata Usaha.
| Setiap surat masuk & surat keluar harus disetujui Kepala TU dulu.
|
| surat_masuk : status_konfirmasi menunggu / disetujui / dikembalikan
|               (surat yang sudah ada sebelumnya otomatis dianggap disetujui)
| surat_keluar: status ditambah "menunggu_kepala_tu" dan "dikembalikan"
|               draft -> menunggu_kepala_tu -> menunggu_approve (Kepsek) -> terbit
| Keduanya    : alasan_konfirmasi, dikonfirmasi_oleh, dikonfirmasi_at
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_masuk', 'status_konfirmasi')) {
                $table->enum('status_konfirmasi', ['menunggu', 'disetujui', 'dikembalikan'])->default('menunggu');
            }
            if (! Schema::hasColumn('surat_masuk', 'alasan_konfirmasi')) {
                $table->text('alasan_konfirmasi')->nullable();
            }
            if (! Schema::hasColumn('surat_masuk', 'dikonfirmasi_oleh')) {
                $table->foreignId('dikonfirmasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('surat_masuk', 'dikonfirmasi_at')) {
                $table->timestamp('dikonfirmasi_at')->nullable();
            }
        });

        // Surat masuk lama sudah berjalan (sudah ke Kepala Sekolah) -> anggap disetujui
        DB::table('surat_masuk')->update(['status_konfirmasi' => 'disetujui']);

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->enum('status', ['draft', 'menunggu_kepala_tu', 'dikembalikan', 'menunggu_approve', 'terbit'])
                ->default('draft')
                ->change();

            if (! Schema::hasColumn('surat_keluar', 'alasan_konfirmasi')) {
                $table->text('alasan_konfirmasi')->nullable();
            }
            if (! Schema::hasColumn('surat_keluar', 'dikonfirmasi_oleh')) {
                $table->foreignId('dikonfirmasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('surat_keluar', 'dikonfirmasi_at')) {
                $table->timestamp('dikonfirmasi_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        DB::table('surat_keluar')->whereIn('status', ['menunggu_kepala_tu', 'dikembalikan'])->update(['status' => 'draft']);

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dikonfirmasi_oleh');
            $table->dropColumn(['alasan_konfirmasi', 'dikonfirmasi_at']);
            $table->enum('status', ['draft', 'menunggu_approve', 'terbit'])->default('draft')->change();
        });

        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dikonfirmasi_oleh');
            $table->dropColumn(['status_konfirmasi', 'alasan_konfirmasi', 'dikonfirmasi_at']);
        });
    }
};

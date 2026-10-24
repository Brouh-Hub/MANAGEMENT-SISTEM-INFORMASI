<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah status "diterima" (staf sudah menerima disposisi, belum mulai dikerjakan)
        DB::statement("ALTER TABLE disposisi MODIFY COLUMN status ENUM('pending', 'diterima', 'diproses', 'selesai', 'diteruskan') NOT NULL DEFAULT 'pending'");

        Schema::table('disposisi', function (Blueprint $table) {
            // Kapan penerima pertama kali membuka disposisi
            if (! Schema::hasColumn('disposisi', 'dibaca_at')) {
                $table->timestamp('dibaca_at')->nullable();
            }

            // Laporan hasil tindak lanjut dari penerima (wajib saat selesai)
            if (! Schema::hasColumn('disposisi', 'laporan_hasil')) {
                $table->text('laporan_hasil')->nullable();
            }

            // Lampiran hasil (opsional), disimpan di storage/app/public/disposisi-hasil
            if (! Schema::hasColumn('disposisi', 'file_hasil')) {
                $table->string('file_hasil')->nullable();
            }

            if (! Schema::hasColumn('disposisi', 'selesai_at')) {
                $table->timestamp('selesai_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        DB::table('disposisi')->where('status', 'diterima')->update(['status' => 'pending']);

        DB::statement("ALTER TABLE disposisi MODIFY COLUMN status ENUM('pending', 'diproses', 'selesai', 'diteruskan') NOT NULL DEFAULT 'pending'");

        Schema::table('disposisi', function (Blueprint $table) {
            $table->dropColumn(['laporan_hasil', 'file_hasil', 'selesai_at']);
        });
    }
};

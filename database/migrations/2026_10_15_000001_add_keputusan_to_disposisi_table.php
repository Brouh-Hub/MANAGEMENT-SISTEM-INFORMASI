<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keputusan Kepala Sekolah: "diarsipkan" (cukup diketahui) dan "ditolak"
        DB::statement("ALTER TABLE disposisi MODIFY COLUMN status ENUM('pending', 'diterima', 'diproses', 'selesai', 'diteruskan', 'diarsipkan', 'ditolak') NOT NULL DEFAULT 'pending'");

        Schema::table('disposisi', function (Blueprint $table) {
            // Alasan keputusan Kepsek (wajib untuk ditolak, opsional untuk diarsipkan)
            if (! Schema::hasColumn('disposisi', 'alasan')) {
                $table->text('alasan')->nullable();
            }

            if (! Schema::hasColumn('disposisi', 'diputuskan_at')) {
                $table->timestamp('diputuskan_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        DB::table('disposisi')
            ->whereIn('status', ['diarsipkan', 'ditolak'])
            ->update(['status' => 'pending']);

        DB::statement("ALTER TABLE disposisi MODIFY COLUMN status ENUM('pending', 'diterima', 'diproses', 'selesai', 'diteruskan') NOT NULL DEFAULT 'pending'");

        Schema::table('disposisi', function (Blueprint $table) {
            $table->dropColumn(['alasan', 'diputuskan_at']);
        });
    }
};

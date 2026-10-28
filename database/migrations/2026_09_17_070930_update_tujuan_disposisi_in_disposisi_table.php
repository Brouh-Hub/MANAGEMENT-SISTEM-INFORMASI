<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tujuan disposisi: dari teks bebas (tujuan_disposisi) menjadi user (tujuan_user_id).
        // Dibuat aman dijalankan ulang: database lama sudah punya kolom ini.
        if (! Schema::hasColumn('disposisi', 'tujuan_user_id')) {
            Schema::table('disposisi', function (Blueprint $table) {
                $table->foreignId('tujuan_user_id')->nullable()->after('kepala_sekolah_id')
                    ->constrained('users')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('disposisi', 'tujuan_disposisi')) {
            Schema::table('disposisi', function (Blueprint $table) {
                $table->dropColumn('tujuan_disposisi');
            });
        }

        if (! Schema::hasColumn('disposisi', 'diteruskan_dari_id')) {
            Schema::table('disposisi', function (Blueprint $table) {
                $table->foreignId('diteruskan_dari_id')->nullable()->constrained('disposisi')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('disposisi', function (Blueprint $table) {
            $table->dropForeign(['tujuan_user_id']);
            $table->dropColumn('tujuan_user_id');
            $table->string('tujuan_disposisi')->nullable();
        });
    }
};

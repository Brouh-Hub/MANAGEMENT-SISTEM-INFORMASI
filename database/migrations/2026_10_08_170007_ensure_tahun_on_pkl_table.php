<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pkl', 'tahun')) {
            Schema::table('pkl', function (Blueprint $table) {
                $table->year('tahun')
                    ->nullable()
                    ->after('tanggal_selesai');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pkl', 'tahun')) {
            Schema::table('pkl', function (Blueprint $table) {
                $table->dropColumn('tahun');
            });
        }
    }
};
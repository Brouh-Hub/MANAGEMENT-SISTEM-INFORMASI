<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Cegah dua surat keluar dengan nomor sama (sebelumnya hanya dicek di validator)
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->unique('nomor_surat', 'surat_keluar_nomor_surat_unique');
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropUnique('surat_keluar_nomor_surat_unique');
        });
    }
};

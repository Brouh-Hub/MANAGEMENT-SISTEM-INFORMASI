<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->string('kepala_sekolah_nama')->nullable();
            $table->string('kepala_sekolah_nip')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->dropColumn([
                'kepala_sekolah_nama',
                'kepala_sekolah_nip',
            ]);
        });
    }
};

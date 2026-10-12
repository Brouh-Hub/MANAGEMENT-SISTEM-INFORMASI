<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sertifikat', function (Blueprint $t) {
            $t->string('nis', 30)->nullable();
            $t->string('tempat_lahir', 100)->nullable();
            $t->date('tanggal_lahir')->nullable();
            $t->string('konsentrasi', 150)->nullable();
            $t->string('mitra', 200)->nullable();
            $t->string('predikat', 30)->nullable();
            $t->string('pimpinan_jabatan', 100)->nullable();
            $t->string('pimpinan_nama', 150)->nullable();
            $t->string('pimpinan_nip', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sertifikat', function (Blueprint $t) {
            $t->dropColumn([
                'nis', 'tempat_lahir', 'tanggal_lahir', 'konsentrasi', 'mitra',
                'predikat', 'pimpinan_jabatan', 'pimpinan_nama', 'pimpinan_nip',
            ]);
        });
    }
};

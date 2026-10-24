<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    | Pengaturan kop surat & data penanda tangan (satu baris saja).
    | Diubah TU lewat menu "Pengaturan Surat", dipakai semua template lewat {{kop_surat}}.
    */
    public function up(): void
    {
        Schema::create('pengaturan_surat', function (Blueprint $table) {
            $table->id();

            // Kop surat
            $table->string('kop_baris_1')->nullable();
            $table->string('kop_baris_2')->nullable();
            $table->string('kop_baris_3')->nullable();
            $table->string('kop_alamat')->nullable();
            $table->string('kop_telepon')->nullable();
            $table->string('kop_laman')->nullable();
            $table->string('kop_email')->nullable();
            $table->string('logo_kiri')->nullable();   // path di storage/app/public
            $table->string('logo_kanan')->nullable();
            $table->unsignedSmallInteger('tinggi_logo')->default(78);

            // Data umum
            $table->string('kota')->nullable();
            $table->string('nama_sekolah')->nullable();

            // Penanda tangan
            $table->string('kepsek_nama')->nullable();
            $table->string('kepsek_pangkat')->nullable();
            $table->string('kepsek_golongan')->nullable();
            $table->string('kepsek_nip')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_surat');
    }
};


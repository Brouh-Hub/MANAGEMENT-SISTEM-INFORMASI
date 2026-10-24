<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Nomor surat keluar otomatis.
|  - penomoran_surat       : format & nomor terakhir per template (diatur TU di Pengaturan Surat)
|                            baris dengan template_surat_id NULL = "Umum" (surat tanpa template)
|  - nomor_surat_reservasi : setiap nomor yang pernah di-generate (UNIQUE -> tidak mungkin dobel)
*/
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penomoran_surat')) {
            Schema::create('penomoran_surat', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_surat_id')->nullable()->unique()
                    ->constrained('template_surat')->cascadeOnDelete();
                $table->string('kode')->nullable();                 // kode klasifikasi, mis. 400.3.8
                $table->string('format');                           // mis. {kode}/{nomor}/SMKN4-TPI/{tahun}
                $table->unsignedTinyInteger('panjang')->default(3); // 3 -> 007
                $table->enum('reset', ['tahunan', 'bulanan', 'tidak'])->default('tahunan');
                $table->unsignedInteger('nomor_terakhir')->default(0);
                $table->string('periode')->nullable();              // 2026 / 2026-10 / -
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('nomor_surat_reservasi')) {
            Schema::create('nomor_surat_reservasi', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_surat')->unique();
                $table->unsignedInteger('nomor_urut');
                $table->foreignId('penomoran_surat_id')->nullable()->constrained('penomoran_surat')->nullOnDelete();
                $table->foreignId('template_surat_id')->nullable()->constrained('template_surat')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('surat_keluar_id')->nullable()->unique()->constrained('surat_keluar')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_surat_reservasi');
        Schema::dropIfExists('penomoran_surat');
    }
};

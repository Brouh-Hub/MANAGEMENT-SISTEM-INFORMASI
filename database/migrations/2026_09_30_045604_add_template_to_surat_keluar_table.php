<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->foreignId('template_surat_id')
                ->nullable()
                ->after('jenis_surat')
                ->constrained('template_surat')
                ->nullOnDelete();

            // Hasil akhir surat yang sudah diisi dari template
            // dan masih boleh diedit oleh Tata Usaha
            $table->longText('isi_surat')
                ->nullable()
                ->after('data_tambahan');
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropForeign(['template_surat_id']);
            $table->dropColumn([
                'template_surat_id',
                'isi_surat',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_surat_fields', function (Blueprint $table) {
            $table->id();

            $table->foreignId('template_surat_id')
                ->constrained('template_surat')
                ->cascadeOnDelete();

            // Contoh: sifat, lampiran, tanggal_acara
            $table->string('key');

            // Contoh: Sifat, Lampiran, Hari / Tanggal Acara
            $table->string('label');

            // Jenis input yang tampil di form
            $table->enum('type', [
                'text',
                'textarea',
                'date',
                'time',
                'number'
            ])->default('text');

            // Apakah field wajib diisi
            $table->boolean('is_required')->default(false);

            // Nilai bawaan, misalnya "Biasa" atau "-"
            $table->text('default_value')->nullable();

            // Urutan tampil pada form
            $table->unsignedInteger('urutan')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Dalam satu template tidak boleh ada key yang sama
            $table->unique([
                'template_surat_id',
                'key'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_surat_fields');
    }
};

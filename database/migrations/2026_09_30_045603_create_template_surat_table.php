<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_surat', function (Blueprint $table) {
            $table->id();

            $table->string('nama_template');
            $table->string('jenis_surat')->nullable();

            // Isi template dalam bentuk HTML dari editor
            $table->longText('isi_template');

            // Daftar placeholder/variabel template
            // Contoh: nomor_surat, tanggal_surat, nama_guru, semester
            $table->json('variabel')->nullable();

            $table->boolean('is_active')->default(true);

            $table->foreignId('user_id_pembuat')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_surat');
    }
};

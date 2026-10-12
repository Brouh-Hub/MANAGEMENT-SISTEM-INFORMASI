<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disposisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_masuk_id')->constrained('surat_masuk')->cascadeOnDelete();
            $table->text('instruksi');
            $table->text('catatan')->nullable();
            $table->foreignId('kepala_sekolah_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('tanggal_disposisi');
            $table->enum('status', ['pending', 'diproses', 'selesai'])->default('pending');
            $table->enum('sifat', ['sangat_segera', 'segera', 'rahasia', 'biasa'])->default('biasa');
            $table->string('tujuan_disposisi');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisi');
    }
};

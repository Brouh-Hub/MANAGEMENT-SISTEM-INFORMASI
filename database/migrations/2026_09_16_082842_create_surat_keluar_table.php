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
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->string('perihal');
            $table->string('tujuan');
            $table->text('alamat_tujuan')->nullable();
            $table->text('keterangan')->nullable();
            $table->date('tanggal_surat');
            $table->string('file_path')->nullable();
            $table->foreignId('user_id_pembuat')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kepala_sekolah_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'menunggu_approve', 'terbit'])->default('draft');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluar');
    }
};

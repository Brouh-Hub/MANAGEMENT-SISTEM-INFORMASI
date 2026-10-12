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
        Schema::create('sertifikat', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_sertifikat')->unique();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_terbit')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('kerjasama_id')->nullable()->constrained('kerjasama')->nullOnDelete();
            $table->foreignId('user_id_penerima')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id_penerbit')->constrained('users')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('template_sertifikat')->cascadeOnDelete();
            $table->enum('status', ['draft', 'terbit'])->default('draft');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikat');
    }
};

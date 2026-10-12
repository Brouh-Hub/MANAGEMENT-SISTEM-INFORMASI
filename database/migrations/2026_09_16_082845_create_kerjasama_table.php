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
        Schema::create('kerjasama', function (Blueprint $table) {
            $table->id();
            $table->string('judul_kerjasama');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('jenis_kerjasama');
            $table->enum('status', ['draft', 'menunggu_persetujuan', 'aktif', 'selesai', 'tertunda'])->default('draft');
            $table->foreignId('industri_id')->constrained('industri')->cascadeOnDelete();
            $table->foreignId('humas_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kepala_sekolah_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_mou_path')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kerjasama');
    }
};

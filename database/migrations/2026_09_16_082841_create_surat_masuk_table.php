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
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->string('perihal');
            $table->string('pengirim');
            $table->text('alamat_asal_pengirim')->nullable();
            $table->date('tanggal_surat');
            $table->date('tanggal_terima');
            $table->string('no_agenda')->unique();
            $table->string('file_path')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_masuk');
    }
};

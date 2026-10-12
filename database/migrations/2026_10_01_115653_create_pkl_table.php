<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkl', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('kerjasama_id');
            $table->unsignedBigInteger('siswa_id');

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->foreign('kerjasama_id')
                ->references('id')
                ->on('kerjasama')
                ->cascadeOnDelete();

            $table->foreign('siswa_id')
                ->references('id')
                ->on('siswa')
                ->cascadeOnDelete();

            $table->unique(
                ['kerjasama_id', 'siswa_id'],
                'pkl_kerjasama_siswa_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkl');
    }
};

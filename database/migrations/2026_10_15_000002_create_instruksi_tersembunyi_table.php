<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    | Instruksi yang dihapus Kepala Sekolah dari daftar "Terakhir Anda pakai".
    | Hanya menyembunyikan saran; riwayat disposisi aslinya tidak ikut terhapus.
    */
    public function up(): void
    {
        Schema::create('instruksi_tersembunyi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('instruksi');
            $table->string('kunci', 64); // sha1 dari instruksi (huruf kecil, spasi dirapikan)
            $table->timestamps();

            $table->unique(['user_id', 'kunci']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instruksi_tersembunyi');
    }
};

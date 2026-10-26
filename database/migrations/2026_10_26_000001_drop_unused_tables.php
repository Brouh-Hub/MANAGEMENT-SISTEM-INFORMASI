<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabel tanpa model/controller aktif (modul berita publik & log akses dihapus).
    public function up(): void
    {
        Schema::dropIfExists('berita');
        Schema::dropIfExists('kategori_berita');
        Schema::dropIfExists('log_akses');
    }

    public function down(): void
    {
        // Tidak dikembalikan: modul sudah dihapus.
    }
};

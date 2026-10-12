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
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->string('nama_penerima')->nullable();
            $table->string('template_key')->nullable();

            // Tidak lagi wajib diisi, karena template dan penerima dipilih lewat form baru
            $table->unsignedBigInteger('kerjasama_id')->nullable()->change();
            $table->unsignedBigInteger('user_id_penerima')->nullable()->change();
            $table->unsignedBigInteger('template_id')->nullable()->change();

            if (! Schema::hasColumn('sertifikat', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->dropColumn(['nama_penerima', 'template_key']);
        });
    }
};

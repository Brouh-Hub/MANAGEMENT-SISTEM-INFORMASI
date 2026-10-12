<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluar_fields', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->string('label');
            $t->enum('type', ['text', 'textarea', 'date', 'number'])->default('text');
            $t->boolean('is_default')->default(false);
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('urutan')->default(0);
            $t->timestamps();
        });

        Schema::table('surat_keluar', function (Blueprint $t) {
            $t->json('data_tambahan')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $t) {
            $t->dropColumn('data_tambahan');
        });

        Schema::dropIfExists('surat_keluar_fields');
    }
};

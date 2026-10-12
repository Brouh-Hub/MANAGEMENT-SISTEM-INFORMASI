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
        Schema::table('surat_keluar', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_keluar', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('surat_keluar', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('disposisi', 'diteruskan_dari_id')) {
            return; // sudah dibuat oleh migrasi sebelumnya
        }

        Schema::table('disposisi', function (Blueprint $table) {
            $table->foreignId('diteruskan_dari_id')->nullable()->constrained('disposisi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        //
    }
};

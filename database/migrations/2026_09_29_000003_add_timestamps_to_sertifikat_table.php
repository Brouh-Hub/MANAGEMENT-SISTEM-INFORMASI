<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sertifikat', function (Blueprint $t) {
            if (! Schema::hasColumn('sertifikat', 'created_at')) {
                $t->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('sertifikat', 'updated_at')) {
                $t->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // sengaja dikosongkan: kolom timestamp dibiarkan
    }
};

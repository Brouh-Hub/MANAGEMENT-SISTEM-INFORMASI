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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['tu', 'humas', 'kepala_sekolah', 'guru', 'siswa'])->default('siswa');
            $table->foreignId('sekolah_id')->nullable()->constrained('sekolah')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sekolah_id']);
            $table->dropColumn(['role', 'sekolah_id']);
        });
    }
};

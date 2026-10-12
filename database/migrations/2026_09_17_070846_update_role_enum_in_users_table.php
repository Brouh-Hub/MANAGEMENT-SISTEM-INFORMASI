<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_admin') DEFAULT 'admin'");

        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('jabatan');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('tu', 'humas', 'kepala_sekolah', 'guru', 'siswa') DEFAULT 'siswa'");
    }
};

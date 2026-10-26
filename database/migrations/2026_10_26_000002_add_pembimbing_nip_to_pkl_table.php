<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pkl', function (Blueprint $table) {
            $table->string('pembimbing_nip', 50)->nullable()->after('pembimbing_pkl');
        });
    }

    public function down(): void
    {
        Schema::table('pkl', function (Blueprint $table) {
            $table->dropColumn('pembimbing_nip');
        });
    }
};

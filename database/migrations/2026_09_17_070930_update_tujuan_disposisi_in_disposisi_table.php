<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('disposisi', function (Blueprint $table) {
            $table->foreignId('diteruskan_dari_id')->nullable()->constrained('disposisi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('disposisi', function (Blueprint $table) {
            $table->dropForeign(['tujuan_user_id']);
            $table->dropColumn('tujuan_user_id');
            $table->string('tujuan_disposisi')->nullable();
        });
    }
};

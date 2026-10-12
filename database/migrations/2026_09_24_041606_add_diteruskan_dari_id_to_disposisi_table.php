<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('disposisi', function (Blueprint $table) {
            $table->foreignId('diteruskan_dari_id')->nullable()->constrained('disposisi')->nullOnDelete();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disposisi', function (Blueprint $table) {
            //
        });
    }
};

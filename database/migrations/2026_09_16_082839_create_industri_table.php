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
        Schema::create('industri', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan');
            $table->text('alamat')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('bidang');
            $table->foreignId('humas_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industri');
    }
};

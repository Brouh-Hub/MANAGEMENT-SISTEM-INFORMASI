<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_logins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('status', 20); // berhasil | gagal | terkunci | logout
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_logins');
    }
};

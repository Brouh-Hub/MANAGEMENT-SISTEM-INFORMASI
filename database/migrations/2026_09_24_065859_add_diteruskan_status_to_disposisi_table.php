<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE disposisi MODIFY COLUMN status ENUM('pending', 'diproses', 'selesai', 'diteruskan') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE disposisi MODIFY COLUMN status ENUM('pending', 'diproses', 'selesai') NOT NULL DEFAULT 'pending'");
    }
};

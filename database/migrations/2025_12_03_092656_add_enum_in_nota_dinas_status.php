<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE nota_dinas MODIFY COLUMN status 
            ENUM('baru', 'menunggu_validasi', 'disetujui', 'ditolak', 'dibaca', 'selesai') 
            NOT NULL DEFAULT 'baru'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE nota_dinas MODIFY COLUMN status 
            ENUM('baru', 'dibalas', 'selesai', 'menunggu_validasi') 
            NOT NULL DEFAULT 'baru'");
    }
};

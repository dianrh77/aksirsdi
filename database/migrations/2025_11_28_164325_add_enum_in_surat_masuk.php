<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE surat_masuks MODIFY COLUMN status 
            ENUM('baru', 'menunggu_manager', 'menunggu_kesra', 'siap_disposisi', 'didisposisi', 'selesai','ditolak_manager') 
            NOT NULL DEFAULT 'baru'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE surat_masuks MODIFY COLUMN status 
            ENUM('ditolak_manager') 
            NOT NULL DEFAULT 'baru'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_internal_docs', function (Blueprint $table) {

            // versi dokumen, mulai dari 1
            $table->integer('version')->default(1)->after('lampiran_pdf');

            // menandai versi mana yang aktif
            $table->boolean('is_active')->default(true)->after('version');
        });
    }

    public function down(): void
    {
        Schema::table('surat_internal_docs', function (Blueprint $table) {
            $table->dropColumn('version');
            $table->dropColumn('is_active');
        });
    }
};

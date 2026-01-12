<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_internal_docs', function (Blueprint $table) {
            $table->string('lampiran_pdf')->nullable()->after('file_pdf');
        });
    }

    public function down(): void
    {
        Schema::table('surat_internal_docs', function (Blueprint $table) {
            $table->dropColumn('lampiran_pdf');
        });
    }
};

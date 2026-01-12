<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota_dinas', function (Blueprint $table) {
            $table->string('lampiran_lain')->nullable()->after('lampiran');
            $table->string('lampiran_lain_nama')->nullable()->after('lampiran_lain');
        });
    }

    public function down(): void
    {
        Schema::table('nota_dinas', function (Blueprint $table) {
            $table->dropColumn(['lampiran_lain', 'lampiran_lain_nama']);
        });
    }
};

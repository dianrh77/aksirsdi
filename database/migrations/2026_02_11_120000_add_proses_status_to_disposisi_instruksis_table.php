<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('disposisi_instruksis', function (Blueprint $table) {
            $table->enum('proses_status', ['lanjut', 'hold'])->default('lanjut')->after('batas_waktu');
            $table->text('hold_reason')->nullable()->after('proses_status');
        });
    }

    public function down(): void
    {
        Schema::table('disposisi_instruksis', function (Blueprint $table) {
            $table->dropColumn(['proses_status', 'hold_reason']);
        });
    }
};

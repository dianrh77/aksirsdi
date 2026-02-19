<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->unsignedBigInteger('validator_manager_id')->nullable()->after('pengirim_id');
        });
    }

    public function down(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropColumn('validator_manager_id');
        });
    }
};

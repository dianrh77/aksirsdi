<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->foreignId('position_id')
                ->after('created_by')
                ->nullable()
                ->constrained('positions')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('position_id');
        });
    }
};

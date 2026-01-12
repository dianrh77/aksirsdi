<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->enum('jenis_disposisi', ['biasa', 'penting', 'rahasia'])->default('biasa')->after('status');
        });
    }

    public function down()
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropColumn('jenis_disposisi');
        });
    }
};

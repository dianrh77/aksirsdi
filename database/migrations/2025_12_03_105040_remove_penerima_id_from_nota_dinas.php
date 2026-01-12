<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('nota_dinas', function (Blueprint $table) {
            $table->dropColumn('penerima_id');
        });
    }

    public function down()
    {
        Schema::table('nota_dinas', function (Blueprint $table) {
            $table->unsignedBigInteger('penerima_id')->nullable();
        });
    }
};

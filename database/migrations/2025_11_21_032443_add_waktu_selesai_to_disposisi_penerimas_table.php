<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('disposisi_penerimas', function (Blueprint $table) {
            $table->timestamp('waktu_selesai')->nullable()->after('waktu_tindak');
        });
    }

    public function down()
    {
        Schema::table('disposisi_penerimas', function (Blueprint $table) {
            $table->dropColumn('waktu_selesai');
        });
    }
};

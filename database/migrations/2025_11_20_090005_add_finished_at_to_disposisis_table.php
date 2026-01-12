<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->timestamp('finished_at')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropColumn('finished_at');
        });
    }
};

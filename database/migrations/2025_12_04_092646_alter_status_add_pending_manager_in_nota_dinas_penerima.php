<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('nota_dinas_penerima', function (Blueprint $table) {
            $table->enum('status', [
                'baru',
                'dibaca',
                'diproses',
                'dibalas',
                'selesai',
                'pending_manager'
            ])->default('baru')->change();
        });
    }

    public function down()
    {
        Schema::table('nota_dinas_penerima', function (Blueprint $table) {
            $table->enum('status', [
                'baru',
                'dibaca',
                'diproses',
                'dibalas',
                'selesai'
            ])->default('baru')->change();
        });
    }
};

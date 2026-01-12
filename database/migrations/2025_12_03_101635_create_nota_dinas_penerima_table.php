<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('nota_dinas_penerima', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('nota_dinas_id'); // FK manual
        $table->unsignedBigInteger('user_id');       // penerima nota

        $table->enum('tipe', ['langsung', 'validasi', 'delegasi'])->default('langsung');

        $table->enum('status', ['baru', 'dibaca', 'diproses', 'dibalas', 'selesai'])
              ->default('baru');

        $table->timestamp('waktu_dibaca')->nullable();
        $table->timestamp('waktu_selesai')->nullable();

        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('nota_dinas_penerima');
}

};

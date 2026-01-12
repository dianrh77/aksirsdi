<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nota_dinas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pengirim_id');   // tanpa FK
            $table->unsignedBigInteger('penerima_id');   // tanpa FK

            $table->string('nomor_nota')->unique();
            $table->string('judul');
            $table->longText('isi');

            $table->string('lampiran')->nullable();

            $table->enum('status', ['baru', 'dibalas', 'selesai'])->default('baru');

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('nota_dinas');
    }
};

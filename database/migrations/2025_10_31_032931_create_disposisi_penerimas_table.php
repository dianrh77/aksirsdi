<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('disposisi_penerimas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('disposisi_id')->nullable();
            $table->unsignedBigInteger('penerima_id')->nullable();
            $table->string('status', 50)->default('Belum Dibaca'); // Belum Dibaca, Dibaca, Ditindaklanjuti
            $table->timestamp('waktu_baca')->nullable();
            $table->timestamp('waktu_tindak')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisi_penerimas');
    }
};

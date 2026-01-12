<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('disposisi_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('disposisi_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('aksi', 100)->nullable(); // Dibuat, Diteruskan, Dibaca, Ditindaklanjuti
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisi_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('disposisi_instruksis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('disposisi_id');
            $table->unsignedBigInteger('direktur_id');
            $table->enum('jenis_direktur', ['utama', 'umum']);
            $table->text('instruksi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisi_instruksis');
    }
};

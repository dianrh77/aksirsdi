<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('disposisis', function (Blueprint $table) {
            $table->id();
            $table->string('no_disposisi', 50)->unique();
            $table->unsignedBigInteger('surat_id')->nullable(); // relasi ke surat masuk / keluar
            $table->unsignedBigInteger('pengirim_id')->nullable(); // user pengirim
            $table->text('catatan')->nullable();
            $table->string('status', 50)->default('Dibuat'); // Dibuat, Diteruskan, Selesai
            $table->timestamp('tanggal_disposisi')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisis');
    }
};

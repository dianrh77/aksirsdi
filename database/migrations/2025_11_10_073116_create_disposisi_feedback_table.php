<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('disposisi_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposisi_penerima_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->text('feedback');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposisi_feedback');
    }
};

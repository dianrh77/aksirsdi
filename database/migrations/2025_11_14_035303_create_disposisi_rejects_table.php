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
        Schema::create('disposisi_rejects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('disposisi_id');
            $table->unsignedBigInteger('direktur_id'); // yang melakukan reject
            $table->text('alasan');
            $table->timestamps();

            $table->foreign('disposisi_id')->references('id')->on('disposisis')->onDelete('cascade');
            $table->foreign('direktur_id')->references('id')->on('users')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposisi_rejects');
    }
};

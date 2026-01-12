<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nota_dinas_balasan', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('nota_dinas_id');  // tanpa FK
            $table->unsignedBigInteger('user_id');        // tanpa FK

            $table->longText('balasan');
            $table->string('lampiran')->nullable();

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('nota_dinas_balasan');
    }
};

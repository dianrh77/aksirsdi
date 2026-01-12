<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surat_internal_docs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat_masuks')->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('templates')->nullOnDelete();

            $table->json('data_isian')->nullable(); // simpan form input dinamis

            $table->string('file_docx')->nullable();
            $table->string('file_pdf')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_internal_docs');
    }
};

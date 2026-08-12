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
        Schema::create('section_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_section_id')
                ->constrained('page_sections')
                ->cascadeOnDelete();

            $table->foreignId('language_id')
                ->constrained('languages')
                ->cascadeOnDelete();

            $table->json('data')->nullable();
            $table->timestamps();
            $table->unique([
                'page_section_id',
                'language_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_translations');
    }
};

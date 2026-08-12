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
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')
                ->constrained('pages')
                ->cascadeOnDelete();

            $table->foreignId('component_id')
                ->constrained('components')
                ->restrictOnDelete();

            $table->unsignedInteger('sort_order')->default(0);
            $table->tinyInteger('is_visible')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};

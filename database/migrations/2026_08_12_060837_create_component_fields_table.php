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
        Schema::create('component_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')
                ->constrained('components')
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('component_fields')
                ->nullOnDelete();

            $table->string('name');
            $table->string('label');
            $table->string('field_type');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_translatable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('component_fields');
    }
};

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
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('post_categories')
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug', 180)->unique();

            $table->string('image')->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->integer('serial')->nullable();
            $table->tinyInteger('status')->default(1);

            $table->foreignId('added_by')->constrained('users')->restrictOnDelete();

            $table->timestamps();

            $table->index(['status', 'serial']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_categories');
    }
};

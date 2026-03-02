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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('name', 150);
            $table->json('name_translations')->nullable();
            $table->text('description')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('value_type', 30)->default('boolean')->comment('boolean|integer|decimal|string|json');
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->index(['value_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};

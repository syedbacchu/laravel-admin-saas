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
            $table->string('name', 255);
            $table->string('group', 150);
            $table->text('description')->nullable();
            $table->string('value_type', 30)->default('boolean')->comment('boolean|integer|decimal|string|json');
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('is_group')->default(0);
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

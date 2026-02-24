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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('module'); // model or table name
            $table->string('name'); // machine key, e.g. seo_title
            $table->string('label'); // human label
            $table->string('type'); // input, textarea, select, checkbox, radio, number, file, boolean
            $table->json('options')->nullable(); // for select/radio/checkbox (json array)
            $table->boolean('is_required')->default(false);
            $table->text('default_value')->nullable();
            $table->string('validation_rules')->nullable(); // e.g. "string|max:255"
            $table->boolean('status')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('show_in')->nullable()->comment('create, update, api');
            $table->timestamps();

            $table->unique(['module', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};

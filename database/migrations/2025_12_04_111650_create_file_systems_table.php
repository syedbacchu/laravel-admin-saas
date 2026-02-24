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
        Schema::create('file_systems', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_name');
            $table->string('type')->nullable();      // mime type
            $table->string('extension')->nullable(); // jpg, png, pdf
            $table->unsignedBigInteger('size')->nullable(); // in bytes
            $table->string('path');           // storage path
            $table->string('full_url')->nullable();           // storage path
            // Image-specific
            $table->string('dimensions')->nullable(); // e.g., 1600x838
            $table->string('alt_text')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            // SEO
            $table->string('seo_keywords')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            // Uploaded by
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_systems');
    }
};

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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('native_name', 120)->nullable();
            $table->string('code', 10)->unique();
            $table->string('direction', 3)->default('ltr')->comment('ltr|rtl');
            $table->unsignedInteger('sort_order')->default(0);
            $table->tinyInteger('status')->default(0)->comment('0=inactive,1=active');
            $table->tinyInteger('is_default')->default(0)->comment('0=no,1=yes');
            $table->timestamps();

            $table->index(['status', 'is_default'], 'lang_status_default_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};


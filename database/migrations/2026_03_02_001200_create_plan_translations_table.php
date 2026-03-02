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
        Schema::create('plan_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('language_id');
            $table->string('name', 150);
            $table->string('subtitle', 255)->nullable();
            $table->timestamps();

            $table->foreign('plan_id', 'plan_trn_plan_fk')
                ->references('id')
                ->on('plans')
                ->cascadeOnDelete();
            $table->foreign('language_id', 'plan_trn_lang_fk')
                ->references('id')
                ->on('languages')
                ->restrictOnDelete();
            $table->unique(['plan_id', 'language_id'], 'plan_trn_plan_lang_uq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_translations');
    }
};


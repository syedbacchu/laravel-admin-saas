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
        Schema::create('feature_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('language_id');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('feature_id', 'feat_trn_feat_fk')
                ->references('id')
                ->on('features')
                ->cascadeOnDelete();
            $table->foreign('language_id', 'feat_trn_lang_fk')
                ->references('id')
                ->on('languages')
                ->restrictOnDelete();
            $table->unique(['feature_id', 'language_id'], 'feat_trn_feat_lang_uq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_translations');
    }
};


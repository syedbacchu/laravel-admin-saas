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
        Schema::create('payment_method_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedBigInteger('language_id');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('payment_method_id', 'pay_method_trn_method_fk')
                ->references('id')
                ->on('payment_methods')
                ->cascadeOnDelete();
            $table->foreign('language_id', 'pay_method_trn_lang_fk')
                ->references('id')
                ->on('languages')
                ->restrictOnDelete();
            $table->unique(['payment_method_id', 'language_id'], 'pay_method_trn_method_lang_uq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_translations');
    }
};


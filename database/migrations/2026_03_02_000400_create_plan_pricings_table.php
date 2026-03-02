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
        Schema::create('plan_pricings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedSmallInteger('term_months')->default(1);
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->string('discount_type', 20)->default('percent')->comment('percent|fixed');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('BDT');
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete();
            $table->unique(['plan_id', 'term_months']);
            $table->index(['term_months', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_pricings');
    }
};

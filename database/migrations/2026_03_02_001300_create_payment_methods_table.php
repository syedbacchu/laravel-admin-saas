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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->json('details_json')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'pay_method_active_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};


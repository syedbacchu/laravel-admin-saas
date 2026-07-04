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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('name', 150);
            $table->string('mobile', 30)->nullable();
            $table->string('email', 180)->nullable();
            $table->string('image', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('rate_status', 30)->default('fixed');
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

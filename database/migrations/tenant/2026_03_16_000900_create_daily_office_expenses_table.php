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
        Schema::create('daily_office_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->date('date');
            $table->string('paid_to', 150);
            $table->string('category', 80);
            $table->unsignedBigInteger('office_id');
            $table->decimal('amount', 14, 2);
            $table->string('remarks', 255);
            $table->string('attachment', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['office_id'], 'daily_office_expenses_office_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_office_expenses');
    }
};

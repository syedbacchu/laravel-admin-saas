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
        Schema::create('salary_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->date('date');
            $table->string('salary_month', 7);
            $table->unsignedBigInteger('paid_to_user_id')->nullable();
            $table->enum('paid_to_user_type', ['employee', 'helper', 'supervisor'])->nullable();
            $table->string('paid_to', 150)->nullable();
            $table->string('category', 80);
            $table->unsignedBigInteger('office_id');
            $table->decimal('amount', 14, 2);
            $table->string('remarks', 255);
            $table->string('attachment', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['office_id'], 'salary_expenses_office_idx');
            $table->index(['paid_to_user_id'], 'salary_expenses_paid_to_user_idx');
            $table->index(['paid_to_user_type'], 'salary_expenses_user_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_expenses');
    }
};

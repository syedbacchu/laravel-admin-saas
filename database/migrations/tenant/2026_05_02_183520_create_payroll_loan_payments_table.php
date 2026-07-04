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
        Schema::create('payroll_loan_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->date('payment_date');
            $table->unsignedBigInteger('loan_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('salary_payment_id')->nullable();
            $table->unsignedBigInteger('salary_expense_id')->nullable();
            $table->string('salary_month', 7)->nullable();
            $table->decimal('principal_amount', 14, 2)->default(0);
            $table->decimal('interest_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2);
            $table->decimal('remaining_balance_before', 14, 2);
            $table->decimal('remaining_balance_after', 14, 2);
            $table->string('payment_method', 50)->default('salary_deduction');
            $table->string('remarks', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['loan_id'], 'payroll_loan_payments_loan_idx');
            $table->index(['employee_id'], 'payroll_loan_payments_employee_idx');
            $table->index(['salary_payment_id'], 'payroll_loan_payments_salary_payment_idx');
            $table->index(['salary_expense_id'], 'payroll_loan_payments_expense_idx');
            $table->index(['salary_month'], 'payroll_loan_payments_month_idx');
            $table->index(['payment_date'], 'payroll_loan_payments_date_idx');
            $table->index(['status', 'created_at'], 'payroll_loan_payments_status_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_loan_payments');
    }
};

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
        Schema::create('payroll_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedInteger('working_day')->default(0);
            $table->string('month', 7);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique(['employee_id', 'month'], 'payroll_attendance_employee_month_unique');
            $table->index(['month'], 'payroll_attendance_month_idx');
            $table->index(['date'], 'payroll_attendance_date_idx');
            $table->index(['status', 'created_at'], 'payroll_attendance_status_created_idx');
        });

        Schema::create('payroll_bonuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('employee_id');
            $table->decimal('bonus_amount', 14, 2);
            $table->string('salary_month', 7);
            $table->string('status', 20)->default('due');
            $table->timestamps();

            $table->index(['salary_month'], 'payroll_bonus_salary_month_idx');
            $table->index(['employee_id'], 'payroll_bonus_employee_idx');
            $table->index(['status'], 'payroll_bonus_status_idx');
            $table->index(['date'], 'payroll_bonus_date_idx');
        });

        Schema::create('payroll_advance_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('employee_id');
            $table->decimal('advance_amount', 14, 2);
            $table->string('salary_month', 7);
            $table->decimal('after_adjustment_amount', 14, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index(['salary_month'], 'payroll_advance_salary_month_idx');
            $table->index(['employee_id'], 'payroll_advance_employee_idx');
            $table->index(['status'], 'payroll_advance_status_idx');
            $table->index(['date'], 'payroll_advance_date_idx');
        });

        Schema::create('payroll_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->date('loan_date');
            $table->unsignedBigInteger('employee_id');
            $table->decimal('loan_amount', 14, 2);
            $table->decimal('monthly_deduction', 14, 2);
            $table->decimal('after_adjustment_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('remaining_balance', 14, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index(['employee_id'], 'payroll_loan_employee_idx');
            $table->index(['status'], 'payroll_loan_status_idx');
            $table->index(['loan_date'], 'payroll_loan_date_idx');
        });

        Schema::create('payroll_generated_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->date('generate_date');
            $table->string('month', 7)->unique();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['generate_date'], 'payroll_generated_salary_date_idx');
            $table->index(['generated_by'], 'payroll_generated_salary_generated_by_idx');
            $table->index(['status', 'created_at'], 'payroll_generated_salary_status_created_idx');
        });

        Schema::create('payroll_salary_sheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('generated_salary_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedInteger('working_day')->default(0);
            $table->string('designation', 120)->nullable();

            $table->decimal('basic_salary', 14, 2)->default(0);
            $table->decimal('house_rent', 14, 2)->default(0);
            $table->decimal('conveyance', 14, 2)->default(0);
            $table->decimal('medical', 14, 2)->default(0);
            $table->decimal('allowance', 14, 2)->default(0);
            $table->decimal('extra_allowance', 14, 2)->default(0);
            $table->decimal('gross_salary', 14, 2)->default(0);
            $table->decimal('bonus', 14, 2)->default(0);
            $table->decimal('total_earnings', 14, 2)->default(0);

            $table->decimal('advance_deduction', 14, 2)->default(0);
            $table->decimal('loan_deduction', 14, 2)->default(0);
            $table->decimal('total_deduction', 14, 2)->default(0);
            $table->decimal('net_payable', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('due_amount', 14, 2)->default(0);

            $table->string('payment_status', 20)->default('unpaid');
            $table->date('paid_date')->nullable();
            $table->timestamps();

            $table->unique(['generated_salary_id', 'employee_id'], 'payroll_salary_sheet_generated_employee_unique');
            $table->index(['employee_id'], 'payroll_salary_sheet_employee_idx');
            $table->index(['payment_status'], 'payroll_salary_sheet_payment_status_idx');
        });

        Schema::create('payroll_salary_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->date('payment_date');
            $table->unsignedBigInteger('salary_sheet_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('salary_month', 7);
            $table->decimal('total_payable', 14, 2);
            $table->decimal('payment_amount', 14, 2);
            $table->decimal('previous_paid', 14, 2)->default(0);
            $table->decimal('remaining_due', 14, 2)->default(0);
            $table->unsignedBigInteger('office_id');
            $table->string('payment_method', 50)->default('cash');
            $table->string('transaction_id', 100)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->unsignedBigInteger('salary_expense_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['status', 'created_at'], 'payroll_salary_payments_status_created_idx');
            $table->index(['salary_sheet_id'], 'payroll_salary_payments_sheet_idx');
            $table->index(['employee_id'], 'payroll_salary_payments_employee_idx');
            $table->index(['salary_month'], 'payroll_salary_payments_month_idx');
            $table->index(['payment_date'], 'payroll_salary_payments_date_idx');
            $table->index(['office_id'], 'payroll_salary_payments_office_idx');
            $table->index(['salary_expense_id'], 'payroll_salary_payments_expense_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_salary_payments');
        Schema::dropIfExists('payroll_salary_sheets');
        Schema::dropIfExists('payroll_generated_salaries');
        Schema::dropIfExists('payroll_loans');
        Schema::dropIfExists('payroll_advance_salaries');
        Schema::dropIfExists('payroll_bonuses');
        Schema::dropIfExists('payroll_attendances');
    }
};

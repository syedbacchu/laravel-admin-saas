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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->enum('employee_type', ['employee', 'helper', 'supervisor'])->default('employee');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('name', 150);
            $table->string('email', 180)->nullable();
            $table->string('mobile', 30);
            $table->string('gender', 20)->default('Male');
            $table->string('blood_group', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('nid', 60)->nullable();
            $table->string('license_no', 80)->nullable();
            $table->date('license_expired_date')->nullable();
            $table->string('designation', 120)->nullable();
            $table->string('address', 255)->nullable();
            $table->decimal('basic_salary', 14, 2)->default(0);
            $table->decimal('house_rent', 14, 2)->default(0);
            $table->decimal('medical', 14, 2)->default(0);
            $table->decimal('allowance', 14, 2)->default(0);
            $table->decimal('extra_allowance', 14, 2)->default(0);
            $table->decimal('conveyance', 14, 2)->default(0);
            $table->decimal('gross_salary', 14, 2)->default(0);
            $table->unsignedBigInteger('vehicle_category_id')->nullable();
            $table->string('image', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->unique(['mobile', 'employee_type'], 'employees_mobile_type_unique');
            $table->index(['employee_type', 'status'], 'employees_type_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

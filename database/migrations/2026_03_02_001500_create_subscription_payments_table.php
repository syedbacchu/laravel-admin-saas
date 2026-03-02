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
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('BDT');
            $table->string('status', 30)->default('pending')->comment('pending|verified|rejected');
            $table->string('payment_reference', 120)->nullable();
            $table->json('method_details')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('subscription_id', 'sub_pay_sub_fk')
                ->references('id')
                ->on('subscriptions')
                ->cascadeOnDelete();
            $table->foreign('tenant_id', 'sub_pay_tenant_fk')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
            $table->foreign('payment_method_id', 'sub_pay_method_fk')
                ->references('id')
                ->on('payment_methods')
                ->restrictOnDelete();
            $table->foreign('verified_by', 'sub_pay_verified_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['tenant_id', 'status'], 'sub_pay_tenant_status_idx');
            $table->index(['payment_method_id', 'status'], 'sub_pay_method_status_idx');
            $table->index(['paid_at', 'verified_at'], 'sub_pay_paid_verified_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};


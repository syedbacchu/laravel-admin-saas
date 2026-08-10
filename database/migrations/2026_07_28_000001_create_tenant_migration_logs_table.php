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
        Schema::create('tenant_migration_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->string('migration_type', 20)->comment('migrate, fresh');
            $table->string('status', 20)->default('pending')->comment('pending, running, completed, failed');
            $table->text('command')->nullable();
            $table->longText('output')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('execution_time', 10, 4)->nullable()->comment('Execution time in seconds');
            $table->integer('migrations_run')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'migration_type']);
            $table->index(['tenant_id', 'status']);
            $table->index(['performed_by']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_migration_logs');
    }
};

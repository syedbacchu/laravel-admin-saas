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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('company_name', 255);
            $table->string('company_username', 180)->unique();
            $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->string('status', 30)->default('provisioning');
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->foreign('owner_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};

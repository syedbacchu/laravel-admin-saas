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
        Schema::create('tenant_databases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('db_name', 120)->unique();
            $table->string('db_host', 190)->default('127.0.0.1');
            $table->unsignedSmallInteger('db_port')->default(3306);
            $table->string('db_username', 120);
            $table->longText('db_password_encrypted');
            $table->string('db_charset', 20)->default('utf8mb4');
            $table->string('db_collation', 40)->default('utf8mb4_unicode_ci');
            $table->longText('tenant_token_encrypted')->nullable();
            $table->string('config_path', 255)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_databases');
    }
};

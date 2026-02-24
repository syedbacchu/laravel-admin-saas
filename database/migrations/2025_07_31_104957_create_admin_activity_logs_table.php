<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 100);
            $table->string('description')->nullable();
            $table->json('data')->nullable();
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes for better query performance
            $table->index(['user_id', 'action']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};

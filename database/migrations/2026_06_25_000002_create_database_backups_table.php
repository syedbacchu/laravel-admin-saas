<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_backups', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->unique();
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->string('database_name');
            $table->string('database_host')->nullable();
            $table->timestamp('backup_created_at');
            $table->boolean('status')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('backup_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_backups');
    }
};
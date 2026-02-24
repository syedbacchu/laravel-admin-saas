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
        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            $table->text('comment');

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('post_comments')
                ->cascadeOnDelete();

            $table->tinyInteger('status')->default(0);
            // 0 = pending, 1 = approved, 2 = rejected

            $table->tinyInteger('visibility')->default(1);
            $table->bigInteger('likes_count')->default(0);

            $table->ipAddress('ip_address')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['post_id', 'status']);
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_comments');
    }
};

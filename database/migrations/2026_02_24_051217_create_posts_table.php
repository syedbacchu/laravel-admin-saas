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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id');
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('post_type')->default('blog'); // article, event, notice, news
            $table->string('thumbnail_img')->nullable();
            $table->string('featured_img')->nullable();
            $table->tinyInteger('visibility')->default(1);
            $table->tinyInteger('is_comment_allow')->default(1);
            $table->tinyInteger('is_featured')->default(0);
            $table->integer('featured_order')->default(0);
            $table->string('status')->default('draft'); // draft, published, scheduled
            $table->timestamp('published_at')->nullable();
            $table->integer('serial')->nullable();

            $table->bigInteger('total_hit')->default(0);
            $table->bigInteger('likes_count')->default(0);
            $table->bigInteger('comments_count')->default(0);
            $table->bigInteger('shares_count')->default(0);

            $table->timestamp('event_date')->nullable();
            $table->timestamp('event_end_date')->nullable();
            $table->text('venue')->nullable();
            $table->text('video_url')->nullable();
            $table->text('photos')->nullable(); // multiple

            $table->text('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
            $table->index(['post_type', 'status', 'published_at']);
            $table->index(['is_featured', 'featured_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

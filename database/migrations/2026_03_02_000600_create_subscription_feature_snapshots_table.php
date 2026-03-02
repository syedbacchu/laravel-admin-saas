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
        Schema::create('subscription_feature_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->string('feature_key', 120);
            $table->string('feature_type', 30)->comment('boolean|integer|decimal|string|json');
            $table->json('feature_value_json')->nullable();
            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->cascadeOnDelete();
            $table->unique(['subscription_id', 'feature_key'], 'sub_feat_snap_sub_feat_uq');
            $table->index(['feature_key', 'feature_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_feature_snapshots');
    }
};

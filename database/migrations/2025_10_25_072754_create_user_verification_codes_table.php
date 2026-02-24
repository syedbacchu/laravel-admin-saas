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
        Schema::create('user_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('code',10);
            $table->tinyInteger('type')->default(1)->comment("1=email, 2=phone, 3=username");
            $table->tinyInteger('attempts')->default(0);
            $table->dateTime('expired_at')->nullable();
            $table->timestamp('blocked_until')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_verification_codes');
    }
};

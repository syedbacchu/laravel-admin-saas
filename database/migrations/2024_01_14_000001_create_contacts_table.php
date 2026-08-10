<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['pending', 'replied'])->default('pending');
            $table->text('reply_message')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->unsignedBigInteger('replied_by')->nullable();
            $table->timestamps();

            $table->foreign('replied_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
            $table->index('email');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contacts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name',100)->nullable();
            $table->string('username',100)->unique()->nullable();
            $table->string('email',180)->unique()->nullable();
            $table->string('phone',20)->unique()->nullable();
            $table->string('phone_code',20)->default('88');
            $table->string('password')->nullable();
            $table->tinyInteger('role_module')->default(3)->comment("1= super admin, 2 = admin, 3 = user");
            $table->unsignedBigInteger('role_id')->nullable();
            $table->tinyInteger('enable_login')->default(1);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('is_private')->default(0);
            $table->unsignedBigInteger('added_by')->default(0);
            $table->tinyInteger('is_phone_verified')->default(0);
            $table->tinyInteger('is_email_verified')->default(0);
            $table->string('image')->nullable();
            $table->string('gender',20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('language', 10)->default('en');
            $table->text('address')->nullable();
            $table->string('country', 10)->nullable();
            $table->string('division', 10)->nullable();
            $table->string('district', 10)->nullable();
            $table->string('thana', 10)->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code',20)->nullable();
            $table->boolean('is_social_login')->default(false);
            $table->string('social_network_id', 180)->nullable();
            $table->string('social_network_type', 20)->nullable();
            $table->tinyInteger('email_notification_status')->default(1);
            $table->tinyInteger('phone_notification_status')->default(1);
            $table->tinyInteger('push_notification_status')->default(1);
            $table->string('facebook_link')->nullable();
            $table->string('linkedin_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('twitter_link')->nullable();
            $table->string('instagram_link')->nullable();
            $table->string('whatsapp_link')->nullable();
            $table->string('telegram_link')->nullable();
            $table->text('device_token')->nullable();
            $table->string('device_type')->nullable();
            $table->string('referral_code')->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->string('password_reset_token', 32)->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Indexes
            $table->index(["phone", "status"]);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};

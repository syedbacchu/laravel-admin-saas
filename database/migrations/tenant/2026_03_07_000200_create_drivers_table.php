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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('name', 120);
            $table->string('phone', 30)->nullable();
            $table->string('license_no', 80)->nullable();
            $table->string('nid_no', 40)->nullable();
            $table->date('joining_date')->nullable();
            $table->string('address', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->nullOnDelete();
            $table->unique('license_no');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};

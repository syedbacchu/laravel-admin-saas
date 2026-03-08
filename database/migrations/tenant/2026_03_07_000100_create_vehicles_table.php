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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('registration_no', 80)->unique();
            $table->string('vehicle_type', 80)->nullable();
            $table->string('brand', 80)->nullable();
            $table->string('model', 80)->nullable();
            $table->unsignedSmallInteger('manufacturing_year')->nullable();
            $table->string('color', 50)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

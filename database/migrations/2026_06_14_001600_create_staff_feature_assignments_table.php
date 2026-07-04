<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_feature_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->string('feature_key');
            $table->tinyInteger('is_accessible')->default(1);
            $table->timestamps();

            $table->unique(['staff_id', 'feature_key'], 'staff_feature_assignments_uq');
            $table->index(['staff_id'], 'staff_feature_assignments_staff_idx');
            $table->foreign('staff_id', 'staff_feature_assignments_staff_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_feature_assignments');
    }
};

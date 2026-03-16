<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type', 30)->nullable()->after('role_module');
                $table->index(['user_type'], 'users_user_type_idx');
            }

            if (!Schema::hasColumn('users', 'tenant_driver_id')) {
                $table->unsignedBigInteger('tenant_driver_id')->nullable()->after('user_type');
                $table->index(['tenant_driver_id'], 'users_tenant_driver_id_idx');
            }
        });

        DB::table('users')
            ->where('role_module', enum(UserRole::USER_ROLE))
            ->where('parent_id', 0)
            ->whereNull('user_type')
            ->update(['user_type' => 'owner']);

        DB::table('users')
            ->where('role_module', enum(UserRole::USER_ROLE))
            ->where('parent_id', '>', 0)
            ->whereNull('user_type')
            ->update(['user_type' => 'staff']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tenant_driver_id')) {
                $table->dropIndex('users_tenant_driver_id_idx');
                $table->dropColumn('tenant_driver_id');
            }

            if (Schema::hasColumn('users', 'user_type')) {
                $table->dropIndex('users_user_type_idx');
                $table->dropColumn('user_type');
            }
        });
    }
};


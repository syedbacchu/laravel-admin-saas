<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
                'role_module' => UserRole::SUPER_ADMIN_ROLE,
                'status' => StatusEnum::ACTIVE,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
                'role_module' => UserRole::ADMIN_ROLE,
                'status' => StatusEnum::ACTIVE,
            ]
        );
    }
}

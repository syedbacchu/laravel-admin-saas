<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('divisions')->truncate();
        Schema::enableForeignKeyConstraints();

        $items = [
            ['code' => 'DH', 'name' => 'DHAKA', 'status' => 1, 'created_at' => now()],
            ['code' => 'CT', 'name' => 'Chattogram', 'status' => 1, 'created_at' => now()],
            ['code' => 'RJ', 'name' => 'Rajshahi', 'status' => 1, 'created_at' => now()],
            ['code' => 'KH', 'name' => 'Khulna', 'status' => 1, 'created_at' => now()],
            ['code' => 'BR', 'name' => 'Barishal', 'status' => 1, 'created_at' => now()],
            ['code' => 'SY', 'name' => 'Sylhet', 'status' => 1, 'created_at' => now()],
            ['code' => 'RG', 'name' => 'Rangpur', 'status' => 1, 'created_at' => now()],
            ['code' => 'MY', 'name' => 'Mymensingh', 'status' => 1, 'created_at' => now()],
        ];
        foreach (array_chunk($items, 50) as $chunk) {
            DB::table('divisions')->insert($chunk);
        }
    }
}

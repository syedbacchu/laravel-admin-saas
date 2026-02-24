<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        Tag::firstOrCreate(['slug' => 'general'], [
            'name' => 'General',
            'added_by' => 1,
        ]);
    }
}

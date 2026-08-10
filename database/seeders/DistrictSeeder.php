<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('districts')->truncate();
        Schema::enableForeignKeyConstraints();

        $districts = [
            // Dhaka Division
            ['code' => 'DH', 'name' => 'DHAKA', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'GZ', 'name' => 'Gazipur', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'NG', 'name' => 'Narayanganj', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'MG', 'name' => 'Manikganj', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'TA', 'name' => 'Tangail', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'MP', 'name' => 'Madaripur', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'KI', 'name' => 'Kishoreganj', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'RB', 'name' => 'Rajbari', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'SP', 'name' => 'Shariatpur', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'FP', 'name' => 'Faridpur', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'NS', 'name' => 'Narsingdi', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'MU', 'name' => 'Munshiganj', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],
            ['code' => 'GP', 'name' => 'Gopalganj', 'status' => 1, 'division_code' => 'DH', 'created_at' => now()],

            // Chattogram Division
            ['code' => 'BB', 'name' => 'Bandarban', 'status' => 1, 'division_code' => 'CT', 'created_at' => now()],
            ['code' => 'BR', 'name' => 'Brahmanbaria', 'status' => 1, 'division_code' => 'CT', 'created_at' => now()],
            ['code' => 'CP', 'name' => 'Chandpur', 'status' => 1, 'division_code' => 'CT', 'created_at' => now()],
            ['code' => 'CG', 'name' => 'Chattogram', 'status' => 1, 'division_code' => 'CT', 'created_at' => now()],
            ['code' => 'CB', 'name' => "Cox's Bazar", 'status' => 1, 'division_code' => 'CT', 'created_at' => now()],
            ['code' => 'CU', 'name' => 'Cumilla', 'status' => 1, 'division_code' => 'CT', 'created_at' => now()],
            ['code' => 'FE', 'name' => 'Feni', 'status' => 1, 'division_code' => 'CT', 'created_at' => now()],
            ['code' => 'KC', 'name' => 'Khagrachari', 'status' => 1, 'division_code' => 'CT', 'created_at' => now()],
            ['code' => 'LX', 'name' => 'Laxmipur', 'status' => 1, 'division_code' => 'CT', 'created_at' => now()],
            ['code' => 'NO', 'name' => 'Noakhali', 'status' => 1, 'division_code' => 'CT', 'created_at' => now()],
            ['code' => 'RM', 'name' => 'Rangamati', 'status' => 1, 'division_code' => 'CT', 'created_at' => now()],

            // Rajshahi Division
            ['code' => 'BO', 'name' => 'Bogura', 'status' => 1, 'division_code' => 'RJ', 'created_at' => now()],
            ['code' => 'CN', 'name' => 'Chapai Nawabganj', 'status' => 1, 'division_code' => 'RJ', 'created_at' => now()],
            ['code' => 'JO', 'name' => 'Joypurhat', 'status' => 1, 'division_code' => 'RJ', 'created_at' => now()],
            ['code' => 'NA', 'name' => 'Naogaon', 'status' => 1, 'division_code' => 'RJ', 'created_at' => now()],
            ['code' => 'NT', 'name' => 'Natore', 'status' => 1, 'division_code' => 'RJ', 'created_at' => now()],
            ['code' => 'PB', 'name' => 'Pabna', 'status' => 1, 'division_code' => 'RJ', 'created_at' => now()],
            ['code' => 'RJ', 'name' => 'Rajshahi', 'status' => 1, 'division_code' => 'RJ', 'created_at' => now()],
            ['code' => 'SI', 'name' => 'Sirajganj', 'status' => 1, 'division_code' => 'RJ', 'created_at' => now()],

            // Khulna Division
            ['code' => 'BG', 'name' => 'Bagerhat', 'status' => 1, 'division_code' => 'KH', 'created_at' => now()],
            ['code' => 'CH', 'name' => 'Chuadanga', 'status' => 1, 'division_code' => 'KH', 'created_at' => now()],
            ['code' => 'JA', 'name' => 'Jashore', 'status' => 1, 'division_code' => 'KH', 'created_at' => now()],
            ['code' => 'JN', 'name' => 'Jhenaidah', 'status' => 1, 'division_code' => 'KH', 'created_at' => now()],
            ['code' => 'KH', 'name' => 'Khulna', 'status' => 1, 'division_code' => 'KH', 'created_at' => now()],
            ['code' => 'KS', 'name' => 'Kushtia', 'status' => 1, 'division_code' => 'KH', 'created_at' => now()],
            ['code' => 'MA', 'name' => 'Magura', 'status' => 1, 'division_code' => 'KH', 'created_at' => now()],
            ['code' => 'ME', 'name' => 'Meherpur', 'status' => 1, 'division_code' => 'KH', 'created_at' => now()],
            ['code' => 'NL', 'name' => 'Narail', 'status' => 1, 'division_code' => 'KH', 'created_at' => now()],
            ['code' => 'SK', 'name' => 'Satkhira', 'status' => 1, 'division_code' => 'KH', 'created_at' => now()],

            // Barishal Division
            ['code' => 'BA', 'name' => 'Barguna', 'status' => 1, 'division_code' => 'BR', 'created_at' => now()],
            ['code' => 'BS', 'name' => 'Barishal', 'status' => 1, 'division_code' => 'BR', 'created_at' => now()],
            ['code' => 'BH', 'name' => 'Bhola', 'status' => 1, 'division_code' => 'BR', 'created_at' => now()],
            ['code' => 'JH', 'name' => 'Jhalokathi', 'status' => 1, 'division_code' => 'BR', 'created_at' => now()],
            ['code' => 'PT', 'name' => 'Patuakhali', 'status' => 1, 'division_code' => 'BR', 'created_at' => now()],
            ['code' => 'PR', 'name' => 'Pirojpur', 'status' => 1, 'division_code' => 'BR', 'created_at' => now()],

            // Sylhet Division
            ['code' => 'HA', 'name' => 'Habiganj', 'status' => 1, 'division_code' => 'SY', 'created_at' => now()],
            ['code' => 'MO', 'name' => 'Moulvibazar', 'status' => 1, 'division_code' => 'SY', 'created_at' => now()],
            ['code' => 'SU', 'name' => 'Sunamganj', 'status' => 1, 'division_code' => 'SY', 'created_at' => now()],
            ['code' => 'SY', 'name' => 'Sylhet', 'status' => 1, 'division_code' => 'SY', 'created_at' => now()],

            // Rangpur Division
            ['code' => 'DI', 'name' => 'Dinajpur', 'status' => 1, 'division_code' => 'RG', 'created_at' => now()],
            ['code' => 'GA', 'name' => 'Gaibandha', 'status' => 1, 'division_code' => 'RG', 'created_at' => now()],
            ['code' => 'KU', 'name' => 'Kurigram', 'status' => 1, 'division_code' => 'RG', 'created_at' => now()],
            ['code' => 'LA', 'name' => 'Lalmonirhat', 'status' => 1, 'division_code' => 'RG', 'created_at' => now()],
            ['code' => 'NI', 'name' => 'Nilphamari', 'status' => 1, 'division_code' => 'RG', 'created_at' => now()],
            ['code' => 'PA', 'name' => 'Panchagarh', 'status' => 1, 'division_code' => 'RG', 'created_at' => now()],
            ['code' => 'RA', 'name' => 'Rangpur', 'status' => 1, 'division_code' => 'RG', 'created_at' => now()],
            ['code' => 'TH', 'name' => 'Thakurgaon', 'status' => 1, 'division_code' => 'RG', 'created_at' => now()],

            // Mymensingh Division
            ['code' => 'JP', 'name' => 'Jamalpur', 'status' => 1, 'division_code' => 'MY', 'created_at' => now()],
            ['code' => 'MS', 'name' => 'Mymensingh', 'status' => 1, 'division_code' => 'MY', 'created_at' => now()],
            ['code' => 'NK', 'name' => 'Netrokona', 'status' => 1, 'division_code' => 'MY', 'created_at' => now()],
            ['code' => 'SH', 'name' => 'Sherpur', 'status' => 1, 'division_code' => 'MY', 'created_at' => now()],
        ];

        foreach (array_chunk($districts, 50) as $chunk) {
            DB::table('districts')->insert($chunk);
        }
    }
}

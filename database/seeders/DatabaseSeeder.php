<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Memanggil seeder yang sudah kita buat
        $this->call([
            FacilitySeeder::class,
            RuleSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
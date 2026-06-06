<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            ['name' => 'WiFi', 'category' => 'Konektivitas'],
            ['name' => 'AC', 'category' => 'Kamar'],
            ['name' => 'Kamar Mandi Dalam', 'category' => 'Kamar'],
            ['name' => 'Parkir Motor', 'category' => 'Fasilitas Luar'],
            ['name' => 'Parkir Mobil', 'category' => 'Fasilitas Luar'],
            ['name' => 'Dapur Umum', 'category' => 'Fasilitas Bersama'],
            ['name' => 'Laundry', 'category' => 'Layanan'],
            ['name' => 'CCTV', 'category' => 'Keamanan'],
            ['name' => 'Keamanan 24 Jam', 'category' => 'Keamanan'],
            ['name' => 'Include Listrik', 'category' => 'Biaya'],
            ['name' => 'Kipas', 'category' => 'Kamar'],
        ];

        foreach ($facilities as $fac) {
            DB::table('facilities')->insert([
                'facility_id' => 'FAC-' . strtoupper(Str::random(6)),
                'facility_name' => $fac['name'],
                'category' => $fac['category'],
                'icon_url' => 'default-icon.svg', // Sementara menggunakan nama file default
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
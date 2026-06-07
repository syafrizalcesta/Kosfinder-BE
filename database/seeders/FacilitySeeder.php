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
            ['id' => 'FAC-WIFI01', 'name' => 'WiFi', 'category' => 'Konektivitas'],
            ['id' => 'FAC-AC0001', 'name' => 'AC', 'category' => 'Kamar'],
            ['id' => 'FAC-KMI001', 'name' => 'Kamar Mandi Dalam', 'category' => 'Kamar'],
            ['id' => 'FAC-PKM001', 'name' => 'Parkir Motor', 'category' => 'Fasilitas Luar'],
            ['id' => 'FAC-PKM002', 'name' => 'Parkir Mobil', 'category' => 'Fasilitas Luar'],
            ['id' => 'FAC-DPR001', 'name' => 'Dapur Umum', 'category' => 'Fasilitas Bersama'],
            ['id' => 'FAC-LDR001', 'name' => 'Laundry', 'category' => 'Layanan'],
            ['id' => 'FAC-CTV001', 'name' => 'CCTV', 'category' => 'Keamanan'],
            ['id' => 'FAC-SEC001', 'name' => 'Keamanan 24 Jam', 'category' => 'Keamanan'],
            ['id' => 'FAC-LST001', 'name' => 'Include Listrik', 'category' => 'Biaya'],
            ['id' => 'FAC-FAN001', 'name' => 'Kipas', 'category' => 'Kamar'],
        ];

        foreach ($facilities as $fac) {
            DB::table('facilities')->updateOrInsert(
                ['facility_id' => $fac['id']],
                [
                    'facility_name' => $fac['name'],
                    'category' => $fac['category'],
                    'icon_url' => 'default-icon.svg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
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
            ['id' => 'FAC-WIFI01', 'name' => 'WiFi', 'category' => 'Konektivitas', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835222/icon-wifi_g2fvch.svg'],
            ['id' => 'FAC-AC0001', 'name' => 'AC', 'category' => 'Kamar', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835218/icon-ac_ggigec.svg'],
            ['id' => 'FAC-KMI001', 'name' => 'Kamar Mandi Dalam', 'category' => 'Kamar', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835218/icon-km-dalam_pxlr48.svg'],
            ['id' => 'FAC-PKM001', 'name' => 'Parkir Motor', 'category' => 'Fasilitas Luar', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835221/icon-parkir_fud8aj.svg'],
            ['id' => 'FAC-PKM002', 'name' => 'Parkir Mobil', 'category' => 'Fasilitas Luar', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835221/icon-parkir-mbl_vuot9i.svg'],
            ['id' => 'FAC-DPR001', 'name' => 'Dapur Umum', 'category' => 'Fasilitas Bersama', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835218/icon-dapur_ingowm.svg'],
            ['id' => 'FAC-LDR001', 'name' => 'Laundry', 'category' => 'Layanan', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835218/icon-laundry_e7rbkg.svg'],
            ['id' => 'FAC-CTV001', 'name' => 'CCTV', 'category' => 'Keamanan', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835218/icon-cctv_wk9jld.svg'],
            ['id' => 'FAC-SEC001', 'name' => 'Keamanan 24 Jam', 'category' => 'Keamanan', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835222/icon-security_mih4k4.svg'],
            ['id' => 'FAC-LST001', 'name' => 'Include Listrik', 'category' => 'Biaya', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835220/icon-listrik_ouzvt0.svg'],
            ['id' => 'FAC-FAN001', 'name' => 'Kipas', 'category' => 'Kamar', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835220/icon-kipas_s48dci.svg'],
        ];

        foreach ($facilities as $fac) {
            DB::table('facilities')->updateOrInsert(
                ['facility_id' => $fac['id']],
                [
                    'facility_name' => $fac['name'],
                    'category' => $fac['category'],
                    'icon_url' => $fac['icon'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
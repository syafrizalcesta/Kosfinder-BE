<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Buat Owner
        $ownerId = 'USR-OWN01';
        DB::table('user')->insert([
            'user_id' => $ownerId,
            'user_name' => 'Bapak Kos Agung',
            'role' => 'pemilik',
            'email' => 'agung@kosfinder.com',
            'password_hash' => bcrypt('password123'),
            'auth_provider' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 2. Buat Master Fasilitas
        DB::table('facilities')->insert([
            ['facilities_id' => 'FAC-001', 'facilities_name' => 'WiFi Gratis', 'icon_url' => 'wifi', 'category' => 'Utama', 'created_at' => $now, 'updated_at' => $now],
            ['facilities_id' => 'FAC-002', 'facilities_name' => 'Kamar Mandi Dalam', 'icon_url' => 'km-dalam', 'category' => 'Kamar', 'created_at' => $now, 'updated_at' => $now], // diubah ke 'km-dalam'
            ['facilities_id' => 'FAC-003', 'facilities_name' => 'AC', 'icon_url' => 'ac', 'category' => 'Kamar', 'created_at' => $now, 'updated_at' => $now], // diubah ke 'ac'
            ['facilities_id' => 'FAC-004', 'facilities_name' => 'Parkir Aman', 'icon_url' => 'parkir', 'category' => 'Fasum', 'created_at' => $now, 'updated_at' => $now], // diubah ke 'parkir'
        ]);

        // 3. Buat Master Peraturan (Sesuaikan string icon_url dengan kunci di React)
        DB::table('rules')->insert([
            ['rules_id' => 'RUL-001', 'rules_name' => 'Akses Bebas 24 Jam', 'icon_url' => 'bebas-jam', 'category' => 'Umum', 'created_at' => $now, 'updated_at' => $now], // diubah ke 'bebas-jam'
            ['rules_id' => 'RUL-002', 'rules_name' => 'Dilarang Merokok', 'icon_url' => 'no-smoking', 'category' => 'Kamar', 'created_at' => $now, 'updated_at' => $now],
            ['rules_id' => 'RUL-003', 'rules_name' => 'Dilarang Bawa Hewan', 'icon_url' => 'no-pets', 'category' => 'Khusus', 'created_at' => $now, 'updated_at' => $now], // diubah ke 'no-pets'
        ]);

        // 4. Buat Data Kos
        DB::table('kos')->insert([
            [
                'kos_id' => 'KOS-001',
                'owner_id' => $ownerId,
                'kos_name' => 'Kos Melati Keputih',
                'address' => 'Jl. Keputih Perintis No. 12',
                'latitude' => -7.291346,
                'longitude' => 112.798835,
                'city' => 'Surabaya',
                'description' => 'Kos pria yang sangat tenang, bersih, dan berlokasi sangat strategis di dekat area kampus. Sangat cocok untuk mahasiswa yang menginginkan lingkungan belajar kondusif.',
                'gender_type' => 'Pria',
                'price' => 1200000,
                'has_video' => false,
                'video_url' => null,
                'total_unit' => 10,
                'available_unit' => 3,
                'whatsapp_contact' => '081234567890',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kos_id' => 'KOS-002',
                'owner_id' => $ownerId,
                'kos_name' => 'Kos Putri Gebang',
                'address' => 'Jl. Gebang Putih No. 45',
                'latitude' => -7.286111,
                'longitude' => 112.795278,
                'city' => 'Surabaya',
                'description' => 'Kos putri eksklusif dengan sistem keamanan penuh, lingkungan asri, dekat dengan tempat makan dan minimarket.',
                'gender_type' => 'Wanita',
                'price' => 1500000,
                'has_video' => true,
                'video_url' => 'https://youtube.com/shorts/dummy',
                'total_unit' => 8,
                'available_unit' => 1,
                'whatsapp_contact' => '089876543210',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);

        // 5. Buat Seed Foto Kos (Menggunakan gambar kos asli yang estetik dari Unsplash)
        DB::table('kos_images')->insert([
            // Foto untuk Kos Melati
            ['images_id' => 'IMG-001', 'kos_id' => 'KOS-001', 'images_url' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=800&q=80', 'is_primary' => true, 'created_at' => $now, 'updated_at' => $now],
            ['images_id' => 'IMG-002', 'kos_id' => 'KOS-001', 'images_url' => 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=800&q=80', 'is_primary' => false, 'created_at' => $now, 'updated_at' => $now],
            
            // Foto untuk Kos Gebang
            ['images_id' => 'IMG-003', 'kos_id' => 'KOS-002', 'images_url' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=800&q=80', 'is_primary' => true, 'created_at' => $now, 'updated_at' => $now],
            ['images_id' => 'IMG-004', 'kos_id' => 'KOS-002', 'images_url' => 'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=800&q=80', 'is_primary' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 6. Hubungkan Kos dengan Fasilitas (Pivot)
        DB::table('kos_facilities')->insert([
            ['id' => 'KF-001', 'kos_id' => 'KOS-001', 'facilities_id' => 'FAC-001', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'KF-002', 'kos_id' => 'KOS-001', 'facilities_id' => 'FAC-002', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'KF-003', 'kos_id' => 'KOS-001', 'facilities_id' => 'FAC-004', 'created_at' => $now, 'updated_at' => $now],
            
            ['id' => 'KF-004', 'kos_id' => 'KOS-002', 'facilities_id' => 'FAC-001', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'KF-005', 'kos_id' => 'KOS-002', 'facilities_id' => 'FAC-002', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'KF-006', 'kos_id' => 'KOS-002', 'facilities_id' => 'FAC-003', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 7. Hubungkan Kos dengan Peraturan (Pivot)
        DB::table('kos_rules')->insert([
            ['id' => 'KR-001', 'kos_id' => 'KOS-001', 'rules_id' => 'RUL-001', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'KR-002', 'kos_id' => 'KOS-001', 'rules_id' => 'RUL-002', 'created_at' => $now, 'updated_at' => $now],
            
            ['id' => 'KR-003', 'kos_id' => 'KOS-002', 'rules_id' => 'RUL-002', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'KR-004', 'kos_id' => 'KOS-002', 'rules_id' => 'RUL-003', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
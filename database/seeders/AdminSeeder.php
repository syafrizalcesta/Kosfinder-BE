<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Gunakan updateOrCreate agar seeder aman dijalankan berulang kali
        // tanpa membuat duplikat akun admin.
        User::updateOrCreate(
            // Kunci pencarian: berdasarkan email (unik)
            ['email' => 'kosfinder2026@gmail.com'],
            // Data yang di-set / diperbarui
            [
                // user_id akan di-generate otomatis oleh boot() di User.php
                // kecuali Anda ingin ID yang fixed — uncomment baris di bawah:
                // 'user_id'             => 'USR-ADMIN00001',

                'user_name'           => 'Admin Kosfinder',
                'role'                => 'admin',
                'password_hash'       => Hash::make(env('ADMIN_PASSWORD', 'ganti-password-ini')),
                'auth_provider'       => 1,           // 1 = email & password
                'is_active'           => true,
                'verification_status' => 'verified',  // admin tidak perlu proses verifikasi
                'phone_whatsapp'      => null,
                'google_id'           => null,
            ]
        );

        $this->command->info('✅ Akun admin berhasil di-seed: kosfinder2026@gmail.com');
    }
}

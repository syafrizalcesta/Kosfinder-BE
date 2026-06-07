<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['id' => 'RUL-SMK001', 'name' => 'Dilarang merokok', 'category' => 'Ketertiban', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835220/icon-no-smoking_sk6v4h.svg'],
            ['id' => 'RUL-JAM001', 'name' => 'Terdapat jam malam', 'category' => 'Akses', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835218/icon-jam-malam_tna7g0.svg'],
            ['id' => 'RUL-LJN001', 'name' => 'Dilarang membawa lawan jenis', 'category' => 'Norma', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835220/icon-no-guests_cphpq4.svg'],
            ['id' => 'RUL-PET001', 'name' => 'Dilarang membawa peliharaan', 'category' => 'Kebersihan', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835220/icon-no-pets_mffhl2.svg'],
            ['id' => 'RUL-TAM001', 'name' => 'Tamu dilarang menginap', 'category' => 'Ketertiban', 'icon' => 'https://res.cloudinary.com/djncmuz1d/image/upload/v1780835220/icon-no-stay_s6mbht.svg'],
        ];

        foreach ($rules as $rule) {
            DB::table('rules')->updateOrInsert(
                ['rule_id' => $rule['id']],
                [
                    'rule_name' => $rule['name'],
                    'category' => $rule['category'],
                    'icon_url' => $rule['icon'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
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
            ['id' => 'RUL-SMK001', 'name' => 'Dilarang merokok', 'category' => 'Ketertiban'],
            ['id' => 'RUL-JAM001', 'name' => 'Terdapat jam malam', 'category' => 'Akses'],
            ['id' => 'RUL-LJN001', 'name' => 'Dilarang membawa lawan jenis', 'category' => 'Norma'],
            ['id' => 'RUL-PET001', 'name' => 'Dilarang membawa peliharaan', 'category' => 'Kebersihan'],
            ['id' => 'RUL-TAM001', 'name' => 'Tamu dilarang menginap', 'category' => 'Ketertiban'],
        ];

        foreach ($rules as $rule) {
            DB::table('rules')->updateOrInsert(
                ['rule_id' => $rule['id']],
                [
                    'rule_name' => $rule['name'],
                    'category' => $rule['category'],
                    'icon_url' => 'default-rule.svg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
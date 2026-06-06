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
            ['name' => 'Dilarang merokok', 'category' => 'Ketertiban'],
            ['name' => 'Terdapat jam malam', 'category' => 'Akses'],
            ['name' => 'Dilarang membawa lawan jenis', 'category' => 'Norma'],
            ['name' => 'Dilarang membawa peliharaan', 'category' => 'Kebersihan'],
            ['name' => 'Tamu dilarang menginap', 'category' => 'Ketertiban'],
        ];

        foreach ($rules as $rule) {
            DB::table('rules')->insert([
                'rule_id' => 'RUL-' . strtoupper(Str::random(6)),
                'rule_name' => $rule['name'],
                'category' => $rule['category'],
                'icon_url' => 'default-rule.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
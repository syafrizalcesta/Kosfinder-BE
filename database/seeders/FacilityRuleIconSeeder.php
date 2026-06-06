<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilityRuleIconSeeder extends Seeder
{
    /**
     * Jalankan dengan: php artisan db:seed --class=FacilityRuleIconSeeder
     *
     * Taruh file SVG kamu di: public/icons/
     * Contoh: public/icons/icon-wifi.svg
     */
    public function run(): void
    {
        // ── FASILITAS ────────────────────────────────────────────────
        $facilities = [
            'WiFi'              => 'icon-wifi.svg',
            'AC'                => 'icon-ac.svg',
            'Kamar Mandi Dalam' => 'icon-km-dalam.svg',
            'Parkir Motor'      => 'icon-parkir.svg',
            'Parkir Mobil'      => 'icon-parkir-mbl.svg',
            'Dapur Umum'        => 'icon-dapur.svg',
            'Laundry'           => 'icon-laundry.svg',
            'CCTV'              => 'icon-cctv.svg',
            'Keamanan 24 Jam'   => 'icon-security.svg',
            'Include Listrik'   => 'icon-listrik.svg',
            'Kipas'             => 'icon-kipas.svg',
        ];

        foreach ($facilities as $name => $file) {
            DB::table('facilities')
                ->where('facility_name', $name)
                ->update(['icon_url' => $file]);
        }

        // ── RULES ────────────────────────────────────────────────────
        $rules = [
            'Dilarang merokok'             => 'icon-no-smoking.svg',
            'Terdapat jam malam'           => 'icon-jam-malam.svg',
            'Dilarang membawa lawan jenis' => 'icon-no-guests.svg',
            'Dilarang membawa peliharaan'  => 'icon-no-pets.svg',
            'Tamu dilarang menginap'       => 'icon-no-stay.svg',
        ];

        foreach ($rules as $name => $file) {
            DB::table('rules')
                ->where('rule_name', $name)
                ->update(['icon_url' => $file]);
        }

        $this->command->info('✅ icon_url berhasil diperbarui.');
        $this->command->info('📁 Pastikan file SVG sudah ada di: public/icons/');
        $this->command->newLine();
        $this->command->info('Daftar file yang dibutuhkan di public/icons/:');
        $this->command->info('  Fasilitas: icon-wifi.svg, icon-ac.svg, icon-km-dalam.svg,');
        $this->command->info('             icon-parkir.svg, icon-dapur.svg, icon-laundry.svg,');
        $this->command->info('             icon-cctv.svg, icon-security.svg, icon-listrik.svg, icon-kipas.svg');
        $this->command->info('  Rules    : icon-no-smoking.svg, icon-jam-malam.svg, icon-no-guests.svg,');
        $this->command->info('             icon-no-pets.svg, icon-no-stay.svg');
    }
}

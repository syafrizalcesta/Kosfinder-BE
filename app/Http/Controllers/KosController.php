<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KosController extends Controller
{
    public function index()
    {
        // Ambil data kos beserta foto utamanya saja untuk halaman depan
        $kosList = Kos::all()->map(function ($kos) {
            $primaryImage = DB::table('kos_images')
                ->where('kos_id', $kos->kos_id)
                ->where('is_primary', true)
                ->value('images_url');
                
            $kos->image_url = $primaryImage ?? 'https://placehold.co/600x400';
            return $kos;
        });
        
        return response()->json([
            'success' => true,
            'data' => $kosList
        ]);
    }

    public function show($id)
    {
        // 1. Cari info kos berdasarkan ID
        $kos = DB::table('kos')->where('kos_id', $id)->first();

        if (!$kos) {
            return response()->json(['success' => false, 'message' => 'Kos tidak ditemukan'], 404);
        }

        // 2. Ambil semua galeri foto kos tersebut
        $images = DB::table('kos_images')->where('kos_id', $id)->get();

        // 3. Ambil daftar fasilitas lewat tabel pivot kos_facilities
        $facilities = DB::table('kos_facilities')
            ->join('facilities', 'kos_facilities.facilities_id', '=', 'facilities.facilities_id')
            ->where('kos_facilities.kos_id', $id)
            ->select('facilities.facilities_name', 'facilities.icon_url')
            ->get();

        // 4. Ambil daftar peraturan lewat tabel pivot kos_rules
        $rules = DB::table('kos_rules')
            ->join('rules', 'kos_rules.rules_id', '=', 'rules.rules_id')
            ->where('kos_rules.kos_id', $id)
            ->select('rules.rules_name', 'rules.icon_url')
            ->get();

        // 5. Gabungkan dan kirimkan hasilnya dalam satu paket JSON yang rapi
        return response()->json([
            'success' => true,
            'data' => array_merge((array)$kos, [
                'images' => $images,
                'facilities' => $facilities,
                'rules' => $rules
            ])
        ]);
    }
}
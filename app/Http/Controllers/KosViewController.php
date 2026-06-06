<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KosViewController extends Controller
{
    /**
     * POST /api/kos/{id}/view
     * Dipanggil otomatis saat DetailKos dibuka.
     * Mencatat siapa yang melihat kos ini (user login atau guest via IP).
     */
    public function record(Request $request, $id)
    {
        $exists = DB::table('kos')->where('kos_id', $id)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'Kos tidak ditemukan'], 404);
        }

        $user   = auth('sanctum')->user();
        $userId = $user?->user_id;
        $ip     = $request->ip();

        // Hindari duplikat dalam 5 menit terakhir.
        $recentlyViewed = DB::table('kos_views')
            ->where('kos_id', $id)
            ->where(function ($q) use ($userId, $ip) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('ip_address', $ip)->whereNull('user_id');
                }
            })
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if (!$recentlyViewed) {
            DB::table('kos_views')->insert([
                'kos_id'     => $id,
                'user_id'    => $userId,
                'ip_address' => $ip,
                'created_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * GET /api/kos/history
     * Kembalikan daftar kos yang pernah dilihat user yang sedang login,
     * diurutkan dari yang paling baru. Max 20 item unik.
     * Melampirkan facilities, rules, average_rating, dan available_unit
     * agar card di Riwayat.jsx dapat render dengan benar.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->user_id;

        // Ambil kos unik yang pernah dilihat, dengan timestamp kunjungan terakhir.
        // GROUP BY di level query agar tidak ada duplikat kos.
        $items = DB::table('kos_views as kv')
            ->join('kos', 'kos.kos_id', '=', 'kv.kos_id')
            ->where('kv.user_id', $userId)
            ->select('kos.*', DB::raw('MAX(kv.created_at) as viewed_at'))
            ->groupBy(
                'kos.kos_id', 'kos.owner_id', 'kos.kos_name', 'kos.address',
                'kos.city', 'kos.latitude', 'kos.longitude', 'kos.description',
                'kos.gender_type', 'kos.price', 'kos.total_unit', 'kos.available_unit',
                'kos.whatsapp_contact', 'kos.status', 'kos.created_at', 'kos.updated_at'
            )
            ->orderByDesc(DB::raw('MAX(kv.created_at)'))
            ->limit(20)
            ->get()
            ->map(function ($kos) {
                // Foto utama
                $kos->image_url = DB::table('kos_images')
                    ->where('kos_id', $kos->kos_id)
                    ->where('is_primary', true)
                    ->value('image_url') ?? 'https://placehold.co/600x400';

                // Fasilitas — dibutuhkan untuk chip di Riwayat.jsx
                $kos->facilities = DB::table('kos_facilities')
                    ->join('facilities', 'kos_facilities.facility_id', '=', 'facilities.facility_id')
                    ->where('kos_facilities.kos_id', $kos->kos_id)
                    ->select('facilities.facility_id', 'facilities.facility_name', 'facilities.icon_url')
                    ->get();

                // Rules — dibutuhkan untuk chip di Riwayat.jsx
                $kos->rules = DB::table('kos_rules')
                    ->join('rules', 'kos_rules.rule_id', '=', 'rules.rule_id')
                    ->where('kos_rules.kos_id', $kos->kos_id)
                    ->select('rules.rule_id', 'rules.rule_name', 'rules.icon_url')
                    ->get();

                // Average rating — dibutuhkan untuk bintang di Riwayat.jsx
                $kos->average_rating = DB::table('reviews')
                    ->where('kos_id', $kos->kos_id)
                    ->avg('rating');

                return $kos;
            });

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }
}
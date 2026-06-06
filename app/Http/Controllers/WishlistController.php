<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WishlistController extends Controller
{
    /**
     * GET /api/kos/wishlist
     * Kembalikan semua kos yang di-wishlist user yang sedang login.
     * Melampirkan facilities, rules, average_rating, dan available_unit
     * agar card di UserWishlist.jsx dapat render dengan benar.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->user_id;

        $items = DB::table('wishlists')
            ->where('wishlists.user_id', $userId)
            ->join('kos', 'wishlists.kos_id', '=', 'kos.kos_id')
            ->select('kos.*', 'wishlists.created_at as wishlisted_at')
            ->orderByDesc('wishlists.created_at')
            ->get()
            ->map(function ($kos) {
                // Foto utama
                $kos->image_url = DB::table('kos_images')
                    ->where('kos_id', $kos->kos_id)
                    ->where('is_primary', true)
                    ->value('image_url') ?? 'https://placehold.co/600x400';

                // Fasilitas — dibutuhkan untuk chip & filter di frontend
                $kos->facilities = DB::table('kos_facilities')
                    ->join('facilities', 'kos_facilities.facility_id', '=', 'facilities.facility_id')
                    ->where('kos_facilities.kos_id', $kos->kos_id)
                    ->select('facilities.facility_id', 'facilities.facility_name', 'facilities.icon_url')
                    ->get();

                // Rules — dibutuhkan untuk chip di frontend
                $kos->rules = DB::table('kos_rules')
                    ->join('rules', 'kos_rules.rule_id', '=', 'rules.rule_id')
                    ->where('kos_rules.kos_id', $kos->kos_id)
                    ->select('rules.rule_id', 'rules.rule_name', 'rules.icon_url')
                    ->get();

                // Average rating — dibutuhkan untuk bintang
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

    /**
     * POST /api/kos/wishlist
     * Toggle wishlist — tambah jika belum ada, hapus jika sudah ada.
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'kos_id' => 'required|string|exists:kos,kos_id',
        ]);

        $userId = $request->user()->user_id;
        $kosId  = $request->kos_id;

        $existing = DB::table('wishlists')
            ->where('user_id', $userId)
            ->where('kos_id',  $kosId)
            ->first();

        if ($existing) {
            DB::table('wishlists')->where('id', $existing->id)->delete();

            return response()->json([
                'success'       => true,
                'is_wishlisted' => false,
                'message'       => 'Kos dihapus dari wishlist.',
            ]);
        }

        DB::table('wishlists')->insert([
            'id'         => 'WSH-' . strtoupper(Str::random(8)),
            'user_id'    => $userId,
            'kos_id'     => $kosId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success'       => true,
            'is_wishlisted' => true,
            'message'       => 'Kos ditambahkan ke wishlist.',
        ]);
    }
}
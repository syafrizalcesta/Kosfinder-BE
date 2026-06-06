<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * GET /api/kos/{kos_id}/reviews
     * Ambil semua review milik satu kos — publik, tidak perlu login.
     */
    public function index(string $kos_id)
    {
        $reviews = Review::with(['user:user_id,user_name', 'photos'])
            ->where('kos_id', $kos_id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $reviews,
        ]);
    }

    /**
     * POST /api/reviews
     * Submit review baru — wajib login, hanya role penyewa.
     * Menerima multipart/form-data karena ada upload foto.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kos_id'    => 'required|string|exists:kos,kos_id',
            'rating'    => 'required|numeric|min:1|max:5',
            'comment'   => 'required|string|max:2000',
            'photos'    => 'nullable|array|max:5',
            'photos.*'  => 'image|mimes:jpg,jpeg,png,webp|max:3072', // maks 3MB per foto
        ]);

        // Cegah pemilik kos menulis review di kos sendiri
        if ($request->user()->role === 'pemilik') {
            return response()->json([
                'success' => false,
                'message' => 'Pemilik kos tidak dapat menulis ulasan.',
            ], 403);
        }

        // Cegah user yang sama review kos yang sama lebih dari sekali
        $alreadyReviewed = Review::where('kos_id', $request->kos_id)
            ->where('user_id', $request->user()->user_id)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah pernah menulis ulasan untuk kos ini.',
            ], 422);
        }

        // Simpan review
        $review = Review::create([
            'user_id' => $request->user()->user_id,
            'kos_id'  => $request->kos_id,
            'rating'  => $request->rating,
            'comment' => $request->comment,
        ]);

        // Simpan foto jika ada
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $file) {
                $path = $file->store("reviews/{$review->reviewid}", 'public');

                ReviewPhoto::create([
                    'reviewid'   => $review->reviewid,
                    'photo_url'  => Storage::url($path),
                    'sort_order' => $index,
                ]);
            }
        }

        // Load relasi untuk response
        $review->load(['user:user_id,user_name', 'photos']);

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil dikirim.',
            'data'    => $review,
        ], 201);
    }

    /**
     * POST /api/reviews/{reviewid}/reply
     * Pemilik kos membalas review — wajib login, hanya role pemilik.
     */
    public function reply(Request $request, string $reviewid)
    {
        $request->validate([
            'owner_reply' => 'required|string|max:1000',
        ]);

        // Hanya pemilik yang boleh membalas
        if ($request->user()->role !== 'pemilik') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pemilik kos yang dapat membalas ulasan.',
            ], 403);
        }

        $review = Review::findOrFail($reviewid);

        // Pastikan pemilik hanya bisa balas review di kosnya sendiri
        $isOwner = $review->kos->owner_id === $request->user()->user_id;
        if (!$isOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu bukan pemilik kos ini.',
            ], 403);
        }

        // Cegah duplikat balasan
        if ($review->owner_reply) {
            return response()->json([
                'success' => false,
                'message' => 'Ulasan ini sudah pernah dibalas.',
            ], 422);
        }

        $review->update([
            'owner_reply'      => $request->owner_reply,
            'owner_replied_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil dikirim.',
            'data'    => $review,
        ]);
    }
}
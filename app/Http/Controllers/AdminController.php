<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Middleware guard: hanya user dengan role 'admin' yang boleh akses.
     * Dipanggil dari route group, bukan di constructor agar kompatibel
     * dengan Laravel 11 yang sudah tidak pakai $this->middleware() di constructor.
     */

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/admin/verifications
    // Mengembalikan semua user yang pernah mengajukan verifikasi
    // (status: pending | approved | rejected)
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $users = User::whereIn('verification_status', ['pending', 'approved', 'rejected'])
            ->orderByRaw("CASE verification_status WHEN 'pending' THEN 1 WHEN 'rejected' THEN 2 WHEN 'approved' THEN 3 ELSE 4 END")
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (User $u) => $this->formatUser($u));

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/admin/verifications/{user_id}/approve
    // Setujui pengajuan verifikasi → role berubah menjadi 'pemilik'
    // ─────────────────────────────────────────────────────────────────────────
    public function approve(Request $request, string $userId)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user = User::where('user_id', $userId)
            ->where('verification_status', 'pending')
            ->firstOrFail();

        $user->update([
            'verification_status' => 'approved',
            'role'                => 'pemilik',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pengguna {$user->user_name} berhasil diverifikasi sebagai Pemilik Kos.",
            'data'    => $this->formatUser($user->fresh()),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/admin/verifications/{user_id}/reject
    // Tolak pengajuan verifikasi — role tetap 'pencari'
    // ─────────────────────────────────────────────────────────────────────────
    public function reject(Request $request, string $userId)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user = User::where('user_id', $userId)
            ->where('verification_status', 'pending')
            ->firstOrFail();

        $user->update([
            'verification_status' => 'rejected',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pengajuan verifikasi {$user->user_name} telah ditolak.",
            'data'    => $this->formatUser($user->fresh()),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: format data user untuk response JSON
    // Menambahkan URL publik untuk ktp_image dan selfie_image
    // ─────────────────────────────────────────────────────────────────────────
    private function formatUser(User $user): array
    {
        return [
            'user_id'             => $user->user_id,
            'user_name'           => $user->user_name,
            'email'               => $user->email,
            'phone_whatsapp'      => $user->phone_whatsapp,
            'role'                => $user->role,
            'verification_status' => $user->verification_status,
            'avatar_url'          => $user->avatar_url, // dari accessor di User model
            'ktp_image_url'    => $user->ktp_image_path ?? null,
            'selfie_image_url' => $user->selfie_image_path ?? null,
            'created_at'          => $user->created_at,
        ];
    }
}
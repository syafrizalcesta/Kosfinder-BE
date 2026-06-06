<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    /**
     * Simpan lead saat user menekan "Hubungi Pemilik".
     *
     * POST /api/leads
     * Body (JSON): { "kos_id": "..." }
     * Header: Authorization: Bearer <token>
     */
    public function store(Request $request)
    {
        $request->validate([
            'kos_id' => 'required|string|exists:kos,kos_id',
        ]);

        $userId = Auth::id(); // user_id dari token Sanctum

        // Cek apakah lead untuk kos + user ini sudah ada
        $exists = Lead::where('kos_id', $request->kos_id)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Lead sudah tercatat sebelumnya.',
                'already_exists' => true,
            ], 200);
        }

        $lead = Lead::create([
            'kos_id'  => $request->kos_id,
            'user_id' => $userId,
        ]);

        return response()->json([
            'message'       => 'Lead berhasil disimpan.',
            'already_exists' => false,
            'data'          => $lead,
        ], 201);
    }

    /**
     * Daftar semua lead milik user yang sedang login.
     *
     * GET /api/leads
     * Header: Authorization: Bearer <token>
     */
    public function index()
    {
        $leads = Lead::with('kos')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json(['data' => $leads]);
    }
}
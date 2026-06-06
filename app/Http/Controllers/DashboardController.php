<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/views
     * Total pengunjung (kos_views) untuk semua kos milik pemilik yang login.
     */
    public function views(Request $request)
    {
        $userId = $request->user()->user_id;

        // Ambil semua kos_id milik pemilik ini
        $kosIds = DB::table('kos')
            ->where('owner_id', $userId)
            ->pluck('kos_id');

        if ($kosIds->isEmpty()) {
            return response()->json(['success' => true, 'total' => 0]);
        }

        $total = DB::table('kos_views')
            ->whereIn('kos_id', $kosIds)
            ->count();

        return response()->json(['success' => true, 'total' => $total]);
    }

    /**
     * GET /api/dashboard/leads
     * Total leads + detail (nama user & kos) untuk semua kos milik pemilik yang login.
     */
    public function leads(Request $request)
    {
        $userId = $request->user()->user_id;

        $kosIds = DB::table('kos')
            ->where('owner_id', $userId)
            ->pluck('kos_id');

        if ($kosIds->isEmpty()) {
            return response()->json(['success' => true, 'total' => 0, 'data' => []]);
        }

        $leads = DB::table('leads')
            ->join('kos', 'leads.kos_id', '=', 'kos.kos_id')
            ->join('users', 'leads.user_id', '=', 'users.user_id')
            ->whereIn('leads.kos_id', $kosIds)
            ->select(
                'leads.leads_id',
                'leads.created_at',
                'kos.kos_name',
                'kos.kos_id',
                DB::raw("users.user_name as user_name"),
                'users.user_id'
            )
            ->orderByDesc('leads.created_at')
            ->limit(50)
            ->get()
            ->map(function ($item) {
                return [
                    'leads_id'   => $item->leads_id,
                    'created_at' => $item->created_at,
                    'kos_name'   => $item->kos_name,
                    'kos_id'     => $item->kos_id,
                    'user'       => [
                        'user_id'   => $item->user_id,
                        'user_name' => $item->user_name,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'total'   => $leads->count(),
            'data'    => $leads,
        ]);
    }
}

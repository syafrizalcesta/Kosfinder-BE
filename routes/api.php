<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KosController; 
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\KosViewController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Models\Facility;
use App\Models\Rule;

// ─── Route Publik ────────────────────────────────────────────────────────────

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/kos/{id}/view', [KosViewController::class, 'record']);

Route::post('/send-otp',    [AuthController::class, 'sendOtp']);
Route::post('/verify-otp',  [AuthController::class, 'verifyOtp']);
Route::post('/register',    [AuthController::class, 'register']);
Route::post('/login',       [AuthController::class, 'login']);

// Review publik — siapapun bisa lihat ulasan tanpa login
Route::get('/kos/{kos_id}/reviews', [ReviewController::class, 'index']);

// ─── Route Terlindungi (Sanctum) ─────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // Auth & Profil
    Route::post('/update-avatar',    [AuthController::class, 'updateAvatar']);
    Route::put('/update-profile',    [AuthController::class, 'updateProfile']);
    Route::post('/check-phone',      [AuthController::class, 'checkPhone']);   // ← BARU
    Route::post('/logout',           [AuthController::class, 'logout']);
    Route::post('/upgrade-account',  [AuthController::class, 'upgradeAccount']);
    Route::put('/change-password',   [AuthController::class, 'changePassword']);

    // Wishlist & Riwayat
    Route::get('/kos/wishlist',  [WishlistController::class, 'index']);
    Route::post('/kos/wishlist', [WishlistController::class, 'toggle']);
    Route::get('/kos/history',   [KosViewController::class, 'index']);

    // Manajemen Kos
    Route::post('/kos',        [KosController::class, 'store']);
    Route::get('/my-kos',      [KosController::class, 'myKos']);
    Route::delete('/kos/{id}', [KosController::class, 'destroy']);
    Route::put('/kos/{id}',    [KosController::class, 'update']);

    // Review
    Route::post('/reviews',                    [ReviewController::class, 'store']);
    Route::post('/reviews/{reviewid}/reply',   [ReviewController::class, 'reply']);

    // Leads
    Route::post('/leads', [LeadController::class, 'store']);
    Route::get('/leads',  [LeadController::class, 'index']);

    // Dashboard pemilik
    Route::get('/dashboard/views', [DashboardController::class, 'views']);
    Route::get('/dashboard/leads', [DashboardController::class, 'leads']);

    // ─── Admin: Verifikasi Pengguna ──────────────────────────────────────────
    // Guard role dicek langsung di AdminController@index/approve/reject
    Route::prefix('admin')->group(function () {
        Route::get('/verifications',                       [AdminController::class, 'index']);
        Route::post('/verifications/{user_id}/approve',    [AdminController::class, 'approve']);
        Route::post('/verifications/{user_id}/reject',     [AdminController::class, 'reject']);
    });
});

// ─── Route Publik Lainnya ────────────────────────────────────────────────────

Route::get('/facilities', function () {
    $data = \App\Models\Facility::orderBy('category')->orderBy('facility_name')->get();
    return response()->json(['success' => true, 'data' => $data]);
});

Route::get('/rules', function () {
    $data = \App\Models\Rule::orderBy('category')->orderBy('rule_name')->get();
    return response()->json(['success' => true, 'data' => $data]);
});

Route::get('/kos',      [KosController::class, 'index']);
Route::get('/kos/{id}', [KosController::class, 'show']);
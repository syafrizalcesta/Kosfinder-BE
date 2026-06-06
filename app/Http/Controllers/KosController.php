<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KosController extends Controller
{
    public function index()
    {
        $kosList = DB::table('kos')->get()->map(function ($kos) {
            // Foto utama
            $primaryImage = DB::table('kos_images')
                ->where('kos_id', $kos->kos_id)
                ->where('is_primary', true)
                ->value('image_url');
            $kos->image_url = $primaryImage ?? 'https://placehold.co/600x400';

            // Fasilitas (maks 3 untuk preview kartu + sisanya dihitung)
            $kos->facilities = DB::table('kos_facilities')
                ->join('facilities', 'kos_facilities.facility_id', '=', 'facilities.facility_id')
                ->where('kos_facilities.kos_id', $kos->kos_id)
                ->select('facilities.facility_id', 'facilities.facility_name', 'facilities.icon_url')
                ->get();

            // Rules
            $kos->rules = DB::table('kos_rules')
                ->join('rules', 'kos_rules.rule_id', '=', 'rules.rule_id')
                ->where('kos_rules.kos_id', $kos->kos_id)
                ->select('rules.rule_id', 'rules.rule_name', 'rules.icon_url')
                ->get();

            // Average rating dari tabel reviews
            $kos->average_rating = DB::table('reviews')
                ->where('kos_id', $kos->kos_id)
                ->avg('rating');

            return $kos;
        });

        return response()->json([
            'success' => true,
            'data' => $kosList
        ]);
    }

    public function show(Request $request, $id)
    {
        $kos = DB::table('kos')->where('kos_id', $id)->first();

        if (!$kos) {
            return response()->json(['success' => false, 'message' => 'Kos tidak ditemukan'], 404);
        }

        $images = DB::table('kos_images')->where('kos_id', $id)->get();

        $facilities = DB::table('kos_facilities')
            ->join('facilities', 'kos_facilities.facility_id', '=', 'facilities.facility_id')
            ->where('kos_facilities.kos_id', $id)
            ->select('facilities.facility_id', 'facilities.facility_name', 'facilities.icon_url')
            ->get();

        $rules = DB::table('kos_rules')
            ->join('rules', 'kos_rules.rule_id', '=', 'rules.rule_id')
            ->where('kos_rules.kos_id', $id)
            ->select('rules.rule_id', 'rules.rule_name', 'rules.icon_url')
            ->get();

        // Ambil phone_whatsapp LANGSUNG dari tabel users berdasarkan owner_id.
        // Ini adalah sumber kebenaran (single source of truth) — bukan dari kolom
        // whatsapp_contact di tabel kos, yang merupakan salinan stale dan bisa
        // tidak sinkron jika pemilik mengganti nomornya di profil.
        $ownerWhatsapp = DB::table('users')
            ->where('user_id', $kos->owner_id)
            ->value('phone_whatsapp');

        // Cek wishlist — gunakan auth('sanctum') agar route tidak wajib login (guest tetap bisa akses)
        $isWishlisted = false;
        $authUser = auth('sanctum')->user();
        if ($authUser) {
            $isWishlisted = DB::table('wishlists')
                ->where('user_id', $authUser->user_id)
                ->where('kos_id', $id)
                ->exists();
        }

        // Catat riwayat kunjungan jika user sedang login
        if ($authUser) {
            $this->recordView($authUser->user_id, $id);
        }

        return response()->json([
            'success' => true,
            'data' => array_merge((array) $kos, [
                'images'           => $images,
                'facilities'       => $facilities,
                'rules'            => $rules,
                'is_wishlisted'    => $isWishlisted,
                'whatsapp_contact' => $ownerWhatsapp,
            ]),
        ]);
    }

    // =========================================================================
    // FUNGSI BARU: Menyimpan Data Upload Kos dari Pemilik
    // =========================================================================
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'pemilik') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        DB::beginTransaction();

        try {
            $kosId = 'KOS-' . strtoupper(Str::random(8));

            // 3. Masukkan data ke tabel 'kos'
            DB::table('kos')->insert([
                'kos_id' => $kosId,
                'owner_id' => $user->user_id,
                'kos_name' => $request->kos_name,
                'address' => $request->address,
                'city' => $request->city,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'description' => $request->description,
                'gender_type' => $request->gender_type,
                'price' => $request->price,
                'total_unit' => $request->total_unit, 
                'available_unit' => $request->available_unit,
                'whatsapp_contact' => $user->phone_whatsapp ?? '08000000000',
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Proses Upload Foto
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('kos_images', 'public');
                    $imageId = 'IMG-' . strtoupper(Str::random(8));
                    
                    DB::table('kos_images')->insert([
                        'image_id' => $imageId,
                        'kos_id' => $kosId,
                        'image_url' => asset('storage/' . $path),
                        'is_primary' => $index === 0 ? true : false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 5. Simpan Fasilitas (frontend mengirim facility_id langsung)
            if ($request->has('facilities')) {
                $facilitiesArr = json_decode($request->facilities, true);
                if (is_array($facilitiesArr)) {
                    foreach ($facilitiesArr as $facilityId) {
                        $exists = DB::table('facilities')->where('facility_id', $facilityId)->exists();
                        if ($exists) {
                            DB::table('kos_facilities')->insert([
                                'facility_id' => $facilityId,
                                'kos_id' => $kosId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            // 6. Simpan Peraturan (Rules) - frontend mengirim rule_id langsung
            if ($request->has('rules')) {
                $rulesArr = json_decode($request->rules, true);
                if (is_array($rulesArr)) {
                    foreach ($rulesArr as $ruleId) {
                        $exists = DB::table('rules')->where('rule_id', $ruleId)->exists();
                        if ($exists) {
                            DB::table('kos_rules')->insert([
                                'rule_id' => $ruleId,
                                'kos_id' => $kosId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Properti kos berhasil dipublikasikan!',
                'kos_id' => $kosId
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }
    public function myKos(Request $request)
    {
        $user = $request->user();

        // Ambil kos di mana owner_id sama dengan user_id yang sedang login
        $myKosList = DB::table('kos')
            ->where('owner_id', $user->user_id)
            ->get()
            ->map(function ($kos) {
                // Ambil foto utama untuk ditampilkan di kartu
                $primaryImage = DB::table('kos_images')
                    ->where('kos_id', $kos->kos_id)
                    ->where('is_primary', true)
                    ->value('image_url');
                
                $kos->image_url = $primaryImage ?? 'https://placehold.co/600x400';
                return $kos;
            });
        
        return response()->json([
            'success' => true,
            'data' => $myKosList
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        // Cari kos berdasarkan ID
        $kos = DB::table('kos')->where('kos_id', $id)->first();
        
        if (!$kos) {
            return response()->json(['success' => false, 'message' => 'Properti tidak ditemukan'], 404);
        }

        // Validasi keamanan: Pastikan yang menghapus adalah pemilik aslinya
        if ($kos->owner_id !== $user->user_id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus properti ini'], 403);
        }

        // Hapus data. (Karena kita pakai onDelete('cascade') di migration, 
        // data di kos_images, kos_facilities, dan kos_rules akan otomatis ikut terhapus dari database!)
        DB::table('kos')->where('kos_id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Properti berhasil dihapus']);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $kos = DB::table('kos')->where('kos_id', $id)->first();

        if (!$kos || $kos->owner_id !== $user->user_id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        DB::beginTransaction();
        try {
            // 1. Update data utama
            DB::table('kos')->where('kos_id', $id)->update([
                'kos_name' => $request->kos_name,
                'city' => $request->city,
                'address' => $request->address,
                'price' => $request->price,
                'description' => $request->description,
                'gender_type' => $request->gender_type,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'total_unit' => $request->total_unit,
                'available_unit' => $request->available_unit,
                // Sinkronkan whatsapp_contact dengan phone_whatsapp user terkini
                // agar jika pemilik pernah update nomor di profil, detail kos ikut terbaru
                'whatsapp_contact' => $user->phone_whatsapp ?? '08000000000',
                'updated_at' => now(),
            ]);

            // 2. Refresh Fasilitas (frontend mengirim facility_id langsung)
            DB::table('kos_facilities')->where('kos_id', $id)->delete();
            if ($request->has('facilities')) {
                $facilities = json_decode($request->facilities, true);
                if (is_array($facilities)) {
                    foreach ($facilities as $facilityId) {
                        $exists = DB::table('facilities')->where('facility_id', $facilityId)->exists();
                        if ($exists) {
                            DB::table('kos_facilities')->insert([
                                'facility_id' => $facilityId,
                                'kos_id' => $id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }

            // 3. Refresh Rules (frontend mengirim rule_id langsung)
            DB::table('kos_rules')->where('kos_id', $id)->delete();
            if ($request->has('rules')) {
                $rules = json_decode($request->rules, true);
                if (is_array($rules)) {
                    foreach ($rules as $ruleId) {
                        $exists = DB::table('rules')->where('rule_id', $ruleId)->exists();
                        if ($exists) {
                            DB::table('kos_rules')->insert([
                                'rule_id' => $ruleId,
                                'kos_id' => $id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PRIVATE HELPER: Catat kunjungan ke tabel kos_views
    // Dipanggil oleh show() setiap kali detail kos dibuka oleh user login.
    // =========================================================================
    private function recordView(string $userId, string $kosId): void
    {
        // Cek apakah sudah ada view dari user + kos yang sama dalam 5 menit terakhir.
        // Ini menghindari spam record jika user refresh halaman berkali-kali.
        $recentlyViewed = DB::table('kos_views')
            ->where('user_id', $userId)
            ->where('kos_id', $kosId)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if (!$recentlyViewed) {
            DB::table('kos_views')->insert([
                'kos_id'     => $kosId,
                'user_id'    => $userId,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        }
    }

    // =========================================================================
    // GET /api/kos/wishlist  — Ambil semua kos yang di-wishlist oleh user login
    // Dipanggil oleh: UserWishlist.jsx
    // =========================================================================
    public function getWishlist(Request $request)
    {
        $user = $request->user();

        // Ambil semua kos_id yang ada di wishlist milik user ini,
        // lalu JOIN langsung ke tabel kos agar satu query, tidak N+1.
        $wishlistedKos = DB::table('wishlists')
            ->join('kos', 'wishlists.kos_id', '=', 'kos.kos_id')
            ->where('wishlists.user_id', $user->user_id)
            ->select('kos.*', 'wishlists.created_at as wishlisted_at')
            ->orderByDesc('wishlists.created_at')
            ->get()
            ->map(function ($kos) {
                // Foto utama
                $primaryImage = DB::table('kos_images')
                    ->where('kos_id', $kos->kos_id)
                    ->where('is_primary', true)
                    ->value('image_url');
                $kos->image_url = $primaryImage ?? 'https://placehold.co/600x400';

                // Fasilitas
                $kos->facilities = DB::table('kos_facilities')
                    ->join('facilities', 'kos_facilities.facility_id', '=', 'facilities.facility_id')
                    ->where('kos_facilities.kos_id', $kos->kos_id)
                    ->select('facilities.facility_id', 'facilities.facility_name', 'facilities.icon_url')
                    ->get();

                // Rules
                $kos->rules = DB::table('kos_rules')
                    ->join('rules', 'kos_rules.rule_id', '=', 'rules.rule_id')
                    ->where('kos_rules.kos_id', $kos->kos_id)
                    ->select('rules.rule_id', 'rules.rule_name', 'rules.icon_url')
                    ->get();

                // Average rating
                $kos->average_rating = DB::table('reviews')
                    ->where('kos_id', $kos->kos_id)
                    ->avg('rating');

                return $kos;
            });

        return response()->json([
            'success' => true,
            'data'    => $wishlistedKos,
        ]);
    }

    // =========================================================================
    // GET /api/kos/history  — Ambil riwayat kos yang pernah dilihat user login
    // Dipanggil oleh: Riwayat.jsx
    // Asumsi: ada tabel `kos_views` dengan kolom (user_id, kos_id, viewed_at).
    // Jika nama tabelmu berbeda, sesuaikan di bawah.
    // =========================================================================
    public function getHistory(Request $request)
    {
        $user = $request->user();

        // Ambil riwayat view unik (satu kos hanya muncul sekali, pakai viewed_at terbaru).
        // Subquery MAX agar jika user buka kos yang sama berkali-kali,
        // hanya tampil satu kartu dengan timestamp kunjungan terakhir.
        $historyKos = DB::table('kos_views as v')
            ->join('kos', 'v.kos_id', '=', 'kos.kos_id')
            ->where('v.user_id', $user->user_id)
            ->select('kos.*', DB::raw('MAX(v.viewed_at) as viewed_at'))
            ->groupBy('kos.kos_id')
            ->orderByDesc('viewed_at')
            ->limit(50)   // batasi 50 entri terbaru agar payload tidak membengkak
            ->get()
            ->map(function ($kos) {
                // Foto utama
                $primaryImage = DB::table('kos_images')
                    ->where('kos_id', $kos->kos_id)
                    ->where('is_primary', true)
                    ->value('image_url');
                $kos->image_url = $primaryImage ?? 'https://placehold.co/600x400';

                // Fasilitas
                $kos->facilities = DB::table('kos_facilities')
                    ->join('facilities', 'kos_facilities.facility_id', '=', 'facilities.facility_id')
                    ->where('kos_facilities.kos_id', $kos->kos_id)
                    ->select('facilities.facility_id', 'facilities.facility_name', 'facilities.icon_url')
                    ->get();

                // Rules
                $kos->rules = DB::table('kos_rules')
                    ->join('rules', 'kos_rules.rule_id', '=', 'rules.rule_id')
                    ->where('kos_rules.kos_id', $kos->kos_id)
                    ->select('rules.rule_id', 'rules.rule_name', 'rules.icon_url')
                    ->get();

                // Average rating
                $kos->average_rating = DB::table('reviews')
                    ->where('kos_id', $kos->kos_id)
                    ->avg('rating');

                return $kos;
            });

        return response()->json([
            'success' => true,
            'data'    => $historyKos,
        ]);
    }

    public function toggleWishlist(Request $request)
    {
        $user = $request->user();
        $kosId = $request->kos_id;

        // Cek apakah kos ini sudah ada di wishlist user tersebut
        $existing = DB::table('wishlists')
            ->where('user_id', $user->user_id)
            ->where('kos_id', $kosId)
            ->first();

        if ($existing) {
            // Jika sudah ada, HAPUS (Unlike)
            DB::table('wishlists')->where('id', $existing->id)->delete();
            return response()->json(['success' => true, 'is_wishlisted' => false, 'message' => 'Dihapus dari wishlist']);
        } else {
            // Jika belum ada, TAMBAHKAN (Like)
            // 🔥 Generate ID String manual karena migration menggunakan string
            $wishlistId = 'WSH-' . strtoupper(\Illuminate\Support\Str::random(8));

            DB::table('wishlists')->insert([
                'id' => $wishlistId, // Masukkan ID manual di sini
                'user_id' => $user->user_id,
                'kos_id' => $kosId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return response()->json(['success' => true, 'is_wishlisted' => true, 'message' => 'Ditambahkan ke wishlist']);
        }
    }
}
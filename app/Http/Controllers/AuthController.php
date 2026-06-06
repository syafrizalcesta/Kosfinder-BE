<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class AuthController extends Controller
{
    // ==========================================
    // TAHAP 1: KIRIM KODE OTP KE EMAIL
    // ==========================================
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:50|unique:users,email',
        ], [
            'email.unique' => 'Email ini sudah terdaftar di KosFinder+.'
        ]);

        $otpCode = rand(1000, 9999);

        try {
            Otp::updateOrCreate(
                ['email' => $request->email],
                [
                    'otp' => $otpCode,
                    'expires_at' => Carbon::now()->addMinutes(5)
                ]
            );

            Mail::to($request->email)->send(new OtpMail($otpCode));

            return response()->json([
                'message' => 'Kode OTP berhasil dikirim ke email Anda.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gagal mengirim OTP: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Gagal mengirim email verifikasi. Pastikan konfigurasi .env sudah benar.'
            ], 500);
        }
    }

    // ==========================================
    // TAHAP 2: VERIFIKASI KODE OTP
    // ==========================================
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:4',
        ]);

        $otpRecord = Otp::where('email', $request->email)
                        ->where('otp', $request->otp)
                        ->first();

        if (!$otpRecord || Carbon::parse($otpRecord->expires_at)->isPast()) {
            return response()->json([
                'message' => 'Kode OTP salah atau telah kedaluwarsa!'
            ], 422);
        }

        return response()->json([
            'message' => 'Verifikasi berhasil! Silakan lengkapi profil Anda.'
        ], 200);
    }

    // ==========================================
    // TAHAP 3: REGISTRASI FINAL (SIMPAN USER)
    // ==========================================
    public function register(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:20',
            'email' => 'required|string|email|max:50|unique:users',
            'password' => 'required|string|min:6',
            'phone_whatsapp' => ['required', 'string', 'min:10', 'max:15', 'regex:/^(08|62)[0-9]+$/'],
        ]);

        $user = User::create([
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'phone_whatsapp' => $request->phone_whatsapp,
            'role' => 'pencari',
            'auth_provider' => 1,
            'is_active' => true,
            'verification_status' => 'unverified'
        ]);

        Otp::where('email', $request->email)->delete();

        $token = $user->createToken('kosfinder-auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi akun berhasil!',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // ==========================================
    // UPDATE FOTO PROFIL (AVATAR)
    // ==========================================
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = $request->user();

        try {
            // Hapus avatar lama jika ada
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Simpan avatar baru
            $avatarPath = $request->file('avatar')->store('avatars', 'public');

            $user->update(['avatar_path' => $avatarPath]);

            return response()->json([
                'message' => 'Foto profil berhasil diperbarui.',
                'avatar_url' => asset('storage/' . $avatarPath),
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gagal upload avatar: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan foto profil.'
            ], 500);
        }
    }

    // ==========================================
    // UPDATE DATA PROFIL (NAMA & NO. HP)
    // ==========================================
    public function updateProfile(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:20',
            'phone_whatsapp' => ['required', 'string', 'min:10', 'max:15', 'regex:/^(08|62)[0-9]+$/'],
        ], [
            'user_name.required' => 'Nama pengguna tidak boleh kosong.',
            'phone_whatsapp.regex' => 'Format nomor WhatsApp harus diawali 08 atau 62.',
        ]);

        $user = $request->user();

        try {
            $user->update([
                'user_name' => $request->user_name,
                'phone_whatsapp' => $request->phone_whatsapp,
            ]);

            return response()->json([
                'message' => 'Profil berhasil diperbarui.',
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gagal update profil: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan perubahan.'
            ], 500);
        }
    }

    // ==========================================
    // UPGRADE AKUN KE PEMILIK KOS
    // ==========================================
    public function upgradeAccount(Request $request)
    {
        $request->validate([
            'ktp_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'selfie_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        try {
            $ktpPath = $request->file('ktp_image')->store('verifications', 'public');
            $selfiePath = $request->file('selfie_image')->store('verifications', 'public');

            $user->update([
                'ktp_image_path' => $ktpPath,
                'selfie_image_path' => $selfiePath,
                'verification_status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Dokumen berhasil diunggah. Menunggu verifikasi admin.',
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gagal upload verifikasi: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan dokumen.'
            ], 500);
        }
    }

    // ==========================================
    // API LOGIN
    // ==========================================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'login_error' => ['Email atau Password yang Anda masukkan salah.'],
            ]);
        }

        $token = $user->createToken('kosfinder-auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6',
        ], [
            'new_password.min' => 'Password baru minimal 6 karakter.',
        ]);
 
        $user = $request->user();
 
        // Verifikasi password lama
        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json([
                'message' => 'Password saat ini tidak sesuai.'
            ], 422);
        }
 
        // Pastikan password baru tidak sama dengan yang lama
        if (Hash::check($request->new_password, $user->password_hash)) {
            return response()->json([
                'message' => 'Password baru tidak boleh sama dengan password lama.'
            ], 422);
        }
 
        $user->update([
            'password_hash' => Hash::make($request->new_password)
        ]);
 
        return response()->json([
            'message' => 'Password berhasil diubah.'
        ], 200);
    }
    // ==========================================
    // API LOGOUT
    // ==========================================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logout berhasil!'
        ]);
    }
}
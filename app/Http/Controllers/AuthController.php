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
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;


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

            // Kirim via Brevo HTTP API
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'api-key' => env('BREVO_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => env('MAIL_FROM_NAME', 'KosFinder'),
                    'email' => env('MAIL_FROM_ADDRESS'),
                ],
                'to' => [
                    ['email' => $request->email]
                ],
                'subject' => 'Kode Verifikasi KosFinder+',
                'htmlContent' => "
                    <div style='font-family: sans-serif; max-width: 480px; margin: auto; padding: 40px; background: #f9fafb; border-radius: 16px;'>
                        <h2 style='color: #1d4ed8;'>KosFinder+</h2>
                        <p>Berikut kode OTP untuk verifikasi akun kamu:</p>
                        <div style='font-size: 40px; font-weight: bold; letter-spacing: 12px; color: #1d4ed8; text-align: center; padding: 24px; background: #eff6ff; border-radius: 12px; margin: 24px 0;'>
                            {$otpCode}
                        </div>
                        <p style='color: #6b7280; font-size: 14px;'>Kode berlaku selama <strong>5 menit</strong>. Jangan bagikan kode ini ke siapapun.</p>
                    </div>
                ",
            ]);

            if ($response->failed()) {
                throw new \Exception('Brevo API error: ' . $response->body());
            }

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
            'phone_whatsapp' => [
                'required',
                'string',
                'min:10',
                'max:15',
                'regex:/^(08|62)[0-9]+$/',
                'unique:users,phone_whatsapp',
            ],
        ], [
            'email.unique' => 'Email ini sudah terdaftar di KosFinder+.',
            'phone_whatsapp.unique' => 'Nomor WhatsApp ini sudah terdaftar di KosFinder+.',
            'phone_whatsapp.regex' => 'Format nomor WhatsApp harus diawali 08 atau 62.',
            'phone_whatsapp.min' => 'Nomor WhatsApp terlalu pendek (minimal 10 angka).',
            'user_name.max' => 'Nama terlalu panjang (maksimal 20 karakter).',
            'password.min' => 'Password minimal 6 karakter.',
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
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

            $upload = $cloudinary->uploadApi()->upload($request->file('avatar')->getRealPath(), [
                'folder' => 'kosfinder/avatars'
            ]);

            if ($user->avatar_path) {
                // Hapus gambar lama di Cloudinary jika perlu
            }

            $user->update(['avatar_path' => $upload['secure_url']]);

            return response()->json([
                'message' => 'Foto profil berhasil diperbarui.',
                'avatar_url' => $upload['secure_url'],
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
    // CEK KETERSEDIAAN NOMOR WHATSAPP
    // ==========================================
    public function checkPhone(Request $request)
    {
        $request->validate([
            'phone_whatsapp' => 'required|string',
        ]);

        $user = $request->user();

        $exists = User::where('phone_whatsapp', $request->phone_whatsapp)
            ->where('id', '!=', $user->id)
            ->exists();

        return response()->json([
            'exists' => $exists,
        ], 200);
    }

    // ==========================================
    // UPDATE DATA PROFIL (NAMA & NO. HP)
    // ==========================================
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'user_name' => 'required|string|max:20',
            'phone_whatsapp' => [
                'required',
                'string',
                'min:10',
                'max:15',
                'regex:/^(08|62)[0-9]+$/',
                // Unique kecuali milik user sendiri
                \Illuminate\Validation\Rule::unique('users', 'phone_whatsapp')->ignore($user->id),
            ],
        ], [
            'user_name.required'      => 'Nama pengguna tidak boleh kosong.',
            'phone_whatsapp.regex'    => 'Format nomor WhatsApp harus diawali 08 atau 62.',
            'phone_whatsapp.min'      => 'Nomor WhatsApp terlalu pendek (minimal 10 angka).',
            'phone_whatsapp.unique'   => 'Nomor WhatsApp sudah digunakan oleh akun lain.',
        ]);

        try {
            $user->update([
                'user_name'       => $request->user_name,
                'phone_whatsapp'  => $request->phone_whatsapp,
            ]);

            return response()->json([
                'message' => 'Profil berhasil diperbarui.',
                'user'    => $user
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
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

            $ktpUpload = $cloudinary->uploadApi()->upload($request->file('ktp_image')->getRealPath(), [
                'folder' => 'kosfinder/verifications'
            ]);

            $selfieUpload = $cloudinary->uploadApi()->upload($request->file('selfie_image')->getRealPath(), [
                'folder' => 'kosfinder/verifications'
            ]);

            $user->update([
                'ktp_image_path' => $ktpUpload['secure_url'],
                'selfie_image_path' => $selfieUpload['secure_url'],
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

    // ==========================================
    // GANTI PASSWORD
    // ==========================================
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6',
        ], [
            'new_password.min' => 'Password baru minimal 6 karakter.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json([
                'message' => 'Password saat ini tidak sesuai.'
            ], 422);
        }

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
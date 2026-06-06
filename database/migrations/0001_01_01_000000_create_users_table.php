<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Primary Key
            $table->string('user_id')->primary();
            
            // Kolom Data Diri
            $table->string('user_name', 20);
            $table->string('role', 10)->default('pencari');
            $table->string('email', 50)->unique();
            
            // Password tanpa batasan (20) agar muat untuk enkripsi Bcrypt (60 karakter)
            $table->string('password_hash')->nullable(); 
            
            $table->string('phone_whatsapp', 15)->nullable();
            $table->string('google_id', 50)->nullable();
            $table->integer('auth_provider');
            $table->boolean('is_active')->default(true); // Tipe bit(1) diwakili oleh boolean
            $table->string('ktp_image_path')->nullable(); 
            $table->string('selfie_image_path')->nullable(); 
            // Status: unverified, pending, verified, rejected
            $table->string('verification_status', 20)->default('unverified');
            // Laravel standar: Membuat created_at dan updated_at sekaligus
            $table->timestamps(); 
        });

        // Bawaan default Laravel (Biarkan saja untuk fitur reset password & sesi login)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
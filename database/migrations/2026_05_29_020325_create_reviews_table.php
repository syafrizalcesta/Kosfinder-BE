<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            // UUID sebagai primary key — lebih aman dari auto-increment string
            // Tidak pakai .default() karena Str::uuid() dievaluasi sekali saat migration,
            // bukan per-row. UUID di-generate otomatis oleh model via booted() hook.
            $table->uuid('reviewid')->primary();

            // Foreign keys diseragamkan ke tipe string (sesuai tabel master)
            $table->string('user_id');
            $table->string('kos_id');

            $table->text('comment')->nullable();

            // decimal(2,1) untuk rating 1.0–5.0 — lebih presisi dari float
            // CHECK constraint ditambahkan via DB::statement di bawah
            $table->decimal('rating', 2, 1);

            // ── Balasan pemilik kos ──────────────────────────────────────
            // Pendekatan kolom in-table: cukup untuk satu balasan per review
            // (model Booking.com / Airbnb). Jika butuh thread balasan,
            // pertimbangkan tabel terpisah review_replies di masa depan.
            $table->text('owner_reply')->nullable();
            $table->timestamp('owner_replied_at')->nullable();

            // Soft delete — review yang dihapus tidak hilang dari DB (audit trail)
            $table->softDeletes();

            $table->timestamps();

            // ── Relasi ───────────────────────────────────────────────────
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('kos_id')->references('kos_id')->on('kos')->onDelete('cascade');

            // ── Indeks untuk performa query ──────────────────────────────
            // Sering di-query: semua review milik satu kos, urutkan by created_at
            $table->index(['kos_id', 'created_at']);
        });

        // CHECK constraint untuk rating 1.0–5.0
        // Dilakukan via raw statement karena Blueprint belum support check() di semua driver
        \DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_rating CHECK (rating >= 1.0 AND rating <= 5.0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel review_photos — menyimpan foto-foto yang diupload bersama ulasan.
     *
     * Alasan dipisah dari tabel reviews:
     * - Relasi one-to-many: satu review bisa punya banyak foto
     * - Memudahkan query foto saja tanpa menarik seluruh data review
     * - Memudahkan proses hapus foto individual tanpa update review
     */
    public function up(): void
    {
        Schema::create('review_photos', function (Blueprint $table) {
            $table->uuid('photo_id')->primary();

            // Foreign key ke reviews — cascade delete agar foto terhapus otomatis
            // saat review dihapus (termasuk soft-deleted yang di-force delete)
            $table->uuid('reviewid');

            // URL file setelah di-upload ke storage (bisa path lokal atau cloud URL)
            $table->string('photo_url');

            // Urutan tampil foto dalam ulasan (opsional, untuk sorting manual)
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('reviewid')->references('reviewid')->on('reviews')->onDelete('cascade');

            // Indeks untuk query: ambil semua foto milik satu review
            $table->index('reviewid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_photos');
    }
};
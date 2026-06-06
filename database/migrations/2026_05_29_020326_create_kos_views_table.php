<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kos_views', function (Blueprint $table) {
            $table->id(); // Tetap menggunakan integer auto-increment untuk id kunjungan
            
            // Foreign keys wajib tipe string
            $table->string('kos_id');
            $table->string('user_id')->nullable(); // Boleh null jika yang lihat tamu (belum login)
            
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent(); // Hanya butuh created_at

            // Relasi
            $table->foreign('kos_id')->references('kos_id')->on('kos')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kos_views');
    }
};
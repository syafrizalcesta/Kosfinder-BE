<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->string('id')->primary(); // Primary key string
            
            // Foreign keys tipe string
            $table->string('user_id');
            $table->string('kos_id');
            
            $table->timestamps();

            // Relasi
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('kos_id')->references('kos_id')->on('kos')->onDelete('cascade');
            
            // Mencegah user memasukkan kos yang sama berkali-kali ke wishlist
            $table->unique(['user_id', 'kos_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
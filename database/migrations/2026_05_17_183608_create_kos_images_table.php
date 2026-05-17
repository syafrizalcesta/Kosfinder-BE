<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('kos_images', function (Blueprint $table) {
            $table->string('images_id')->primary();
            $table->string('kos_id'); // Foreign Key ke tabel kos
            $table->string('images_url', 100);
            $table->boolean('is_primary')->default(false); // bit(1) -> boolean
            $table->timestamps();

        // Relasi FK ke tabel kos
            $table->foreign('kos_id')->references('kos_id')->on('kos')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kos_images');
    }
};

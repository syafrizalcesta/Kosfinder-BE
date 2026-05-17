<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void{
        Schema::create('kos', function (Blueprint $table) {
            // Primary Key varchar
            $table->string('kos_id')->primary();
            
            // Foreign Key ke tabel user (pemilik)
            $table->string('owner_id');
            
            $table->string('kos_name', 20);
            $table->string('address', 50);
            $table->float('latitude');
            $table->float('longitude'); // Saya ikuti penulisan di ERD (longtitude)
            $table->string('city', 20);
            $table->text('description')->nullable(); // Ada logo 'N' (Nullable)
            $table->string('gender_type', 15);
            $table->decimal('price', 8, 0);
            $table->boolean('has_video')->default(false); // bit(1) setara dengan boolean
            $table->string('video_url', 100)->nullable(); // Ada logo 'N' (Nullable)
            $table->integer('total_unit'); // int(3)
            $table->integer('available_unit'); // int(3)
            $table->string('whatsapp_contact', 15);
            
            $table->timestamps();

            $table->foreign('owner_id')->references('user_id')->on('user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kos');
    }
};

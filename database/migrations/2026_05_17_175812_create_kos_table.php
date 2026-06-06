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
            
            $table->string('kos_name', 100);
            $table->string('address', 255);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable(); 
            $table->string('city', 50);
            $table->text('description')->nullable(); 
            $table->string('gender_type', 15);
            $table->integer('price');
            $table->boolean('has_video')->default(false); // bit(1) setara dengan boolean
            $table->string('video_url', 100)->nullable(); // Ada logo 'N' (Nullable)
            $table->integer('total_unit'); // int(3)
            $table->integer('available_unit'); // int(3)
            $table->string('whatsapp_contact', 15);
            
            $table->string('status', 15)->default('aktif');
            
            $table->timestamps();

            $table->foreign('owner_id')->references('user_id')->on('users')->onDelete('cascade');
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

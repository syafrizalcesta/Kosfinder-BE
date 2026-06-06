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
            $table->string('image_id')->primary(); 
            
            $table->string('kos_id');
            
            $table->string('image_url', 255); 
            
            $table->boolean('is_primary')->default(false); // 
            $table->timestamps();

            $table->foreign('kos_id')->references('kos_id')->on('kos')->onDelete('cascade');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('kos_images');
    }
};
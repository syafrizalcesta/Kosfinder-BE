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
        Schema::create('kos_facilities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('facilities_id');
            $table->string('kos_id');
            $table->timestamps();

            // Setup Foreign Key
            $table->foreign('facilities_id')->references('facilities_id')->on('facilities')->onDelete('cascade');
            $table->foreign('kos_id')->references('kos_id')->on('kos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kos_facilities');
    }
};

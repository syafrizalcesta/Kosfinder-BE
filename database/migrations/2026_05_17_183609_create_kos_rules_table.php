<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('kos_rules', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('rules_id');
        $table->string('kos_id');
        $table->timestamps();

        // Setup Foreign Key
        $table->foreign('rules_id')->references('rules_id')->on('rules')->onDelete('cascade');
        $table->foreign('kos_id')->references('kos_id')->on('kos')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kos_rules');
    }
};

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
            $table->id();

            $table->string('rule_id'); 
            $table->string('kos_id');
            $table->timestamps();

            $table->foreign('rule_id')->references('rule_id')->on('rules')->onDelete('cascade');
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

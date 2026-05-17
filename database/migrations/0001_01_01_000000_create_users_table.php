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
        Schema::create('user', function (Blueprint $table) { // Ubah 'users' jadi 'user'
        $table->string('user_id')->primary(); // varchar PK
        $table->string('user_name', 20);
        $table->string('role', 10);
        $table->string('email', 50);
        $table->string('password_hash', 60)->nullable(); 
        $table->string('phone_whatsapp', 15)->nullable(); 
        $table->string('google_id', 50)->nullable(); 
        $table->integer('auth_provider'); 
        $table->boolean('is_active')->default(true); 
        $table->timestamps(); 
    });
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

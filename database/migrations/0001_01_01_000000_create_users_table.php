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

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();          // 🔑 Login with phone
            $table->string('otp')->nullable();          // 🔐 OTP for login
            $table->enum('role', ['admin', 'driver', 'rider'])->nullable();

            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

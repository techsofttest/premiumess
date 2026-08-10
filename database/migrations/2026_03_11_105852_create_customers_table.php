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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone', 20)->nullable();

            $table->string('password')->nullable();

            $table->string('google_id')->nullable();

            $table->string('profile_image')->nullable();

            $table->timestamp('email_verified_at')->nullable();

            $table->tinyInteger('status')->default(1)->comment('1 active, 0 blocked');

            $table->timestamp('last_login_at')->nullable();

            $table->string('otp')->nullable();

            $table->timestamp('otp_expires_at')->nullable();

            $table->rememberToken();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

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
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Premium Essence Perfumes LLC');
            $table->text('address')->default('Musaffah M/9, Abu Dhabi, United Arab Emirates');
            $table->string('phone')->default('+971 2 555 1234');
            $table->string('whatsapp')->default('+971 50 123 4567');
            $table->string('email')->default('info@premiumessence.ae');
            $table->string('support_email')->default('support@premiumessence.ae');
            $table->string('working_hours')->default('Mon - Sat: 9:00 AM - 9:00 PM (GST)');
            $table->text('google_maps_link')->nullable()->default('https://maps.google.com/?q=Musaffah+M9+Abu+Dhabi+UAE');
            $table->string('facebook_url')->nullable()->default('https://facebook.com');
            $table->string('instagram_url')->nullable()->default('https://instagram.com');
            $table->string('twitter_url')->nullable()->default('https://twitter.com');
            $table->string('linkedin_url')->nullable()->default('https://linkedin.com');
            $table->string('youtube_url')->nullable()->default('https://youtube.com');
            $table->string('tiktok_url')->nullable()->default('https://tiktok.com');
            $table->string('pinterest_url')->nullable()->default('https://pinterest.com');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};

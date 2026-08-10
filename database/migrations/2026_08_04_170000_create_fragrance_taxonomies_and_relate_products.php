<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fragrance_families', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('fragrance_concentrations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        foreach (DB::table('products')->whereNotNull('family')->where('family', '!=', '')->distinct()->pluck('family') as $name) {
            DB::table('fragrance_families')->insertOrIgnore([
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (DB::table('products')->whereNotNull('concentration')->where('concentration', '!=', '')->distinct()->pluck('concentration') as $name) {
            DB::table('fragrance_concentrations')->insertOrIgnore([
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('fragrance_family_id')->nullable()->after('gender')->constrained('fragrance_families')->nullOnDelete();
            $table->foreignId('fragrance_concentration_id')->nullable()->after('fragrance_family_id')->constrained('fragrance_concentrations')->nullOnDelete();
        });

        foreach (DB::table('products')->whereNotNull('family')->where('family', '!=', '')->get(['id', 'family']) as $product) {
            $familyId = DB::table('fragrance_families')->where('name', $product->family)->value('id');
            DB::table('products')->where('id', $product->id)->update(['fragrance_family_id' => $familyId]);
        }

        foreach (DB::table('products')->whereNotNull('concentration')->where('concentration', '!=', '')->get(['id', 'concentration']) as $product) {
            $concentrationId = DB::table('fragrance_concentrations')->where('name', $product->concentration)->value('id');
            DB::table('products')->where('id', $product->id)->update(['fragrance_concentration_id' => $concentrationId]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['family', 'concentration']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('family')->nullable();
            $table->string('concentration')->nullable();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fragrance_family_id');
            $table->dropConstrainedForeignId('fragrance_concentration_id');
        });

        Schema::dropIfExists('fragrance_concentrations');
        Schema::dropIfExists('fragrance_families');
    }
};

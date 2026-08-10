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
        Schema::table('cms', function (Blueprint $table) {
            $table->renameColumn('page', 'slug');
        });

        Schema::table('cms', function (Blueprint $table) {
            $table->string('slug')->unique()->change();
            $table->longText('content')->change();
            $table->string('meta_title')->nullable()->after('image');
            $table->text('description')->nullable()->after('meta_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'description']);
            $table->dropUnique('cms_slug_unique');
            $table->text('content')->change();
        });

        Schema::table('cms', function (Blueprint $table) {
            $table->renameColumn('slug', 'page');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->string('niche')->nullable()->after('slug');
            $table->string('language', 10)->default('en')->after('niche');
            $table->text('description')->nullable()->after('language');
            $table->string('tts_provider')->default('edge_tts')->after('description');
            $table->string('stock_provider')->default('pexels')->after('tts_provider');
            $table->boolean('approval_required')->default(true)->after('stock_provider');
            $table->json('posting_schedule')->nullable()->after('approval_required');
            $table->json('style_guide')->nullable()->after('posting_schedule');
            $table->json('brand_kit')->nullable()->after('style_guide');
            $table->json('banned_words')->nullable()->after('brand_kit');
            $table->boolean('is_active')->default(true)->after('banned_words');
        });
    }

    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn([
                'niche', 'language', 'description', 'tts_provider',
                'stock_provider', 'approval_required', 'posting_schedule',
                'style_guide', 'brand_kit', 'banned_words', 'is_active',
            ]);
        });
    }
};
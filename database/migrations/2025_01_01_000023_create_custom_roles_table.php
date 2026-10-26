<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dynamic roles that boss creates
        Schema::create('custom_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // "YouTube Head", "Script Writer"
            $table->string('slug')->unique();  // "youtube_head", "script_writer"
            $table->json('permissions');        // ["upload_youtube","write_script","make_video"]
            $table->string('color')->default('gray');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add custom_role_id to users (replaces hardcoded role enum)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('custom_role_id')->nullable()->after('role')->constrained('custom_roles')->nullOnDelete();
        });

        // Channels: add video duration + which platforms this channel posts to
        Schema::table('channels', function (Blueprint $table) {
            $table->string('video_min_duration')->default('1 min')->after('language');
            $table->string('video_max_duration')->default('1 hr')->after('video_min_duration');
            $table->json('target_platforms')->nullable()->after('video_max_duration'); // ["youtube","tiktok","facebook"]
        });
    }

    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn(['video_min_duration', 'video_max_duration', 'target_platforms']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('custom_role_id');
        });
        Schema::dropIfExists('custom_roles');
    }
};
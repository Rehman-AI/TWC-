<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('genre')->nullable();
            $table->string('mood')->nullable();
            $table->unsignedSmallInteger('bpm')->nullable();
            $table->unsignedSmallInteger('duration_seconds')->default(0);
            $table->string('license_type')->default('royalty_free');
            $table->string('drive_file_id')->nullable();
            $table->string('drive_url')->nullable();
            $table->string('local_path')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('channel_music', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('music_id')->constrained('music')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['channel_id', 'music_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_music');
        Schema::dropIfExists('music');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_project_id')->constrained()->cascadeOnDelete();
            $table->string('seo_title', 100)->nullable();
            $table->text('seo_description')->nullable();
            $table->json('tags')->nullable();
            $table->json('hashtags')->nullable();
            $table->string('thumbnail_text', 100)->nullable();
            $table->integer('seo_score')->default(0);
            $table->json('score_breakdown')->nullable();
            $table->json('suggestions')->nullable();
            $table->timestamps();

            $table->unique('video_project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metadata');
    }
};
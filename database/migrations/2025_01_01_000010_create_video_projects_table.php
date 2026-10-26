<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('status')->default('idea');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('language', 10)->default('en');
            $table->unsignedSmallInteger('target_duration')->default(60);
            $table->string('priority')->default('normal');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->string('drive_folder_id')->nullable();
            $table->timestamps();

            $table->index(['channel_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_projects');
    }
};
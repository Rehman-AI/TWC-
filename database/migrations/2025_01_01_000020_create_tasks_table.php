<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('video_projects')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('channels')->cascadeOnDelete();
            $table->string('type');          // script, video, youtube, facebook, instagram, tiktok
            $table->string('status')->default('pending'); // pending, in_progress, done
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('deadline')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            // Task-specific content
            $table->longText('script_content')->nullable();   // For script tasks
            $table->string('video_drive_url')->nullable();    // For video tasks — Drive link
            $table->string('video_drive_file_id')->nullable();
            $table->string('post_url')->nullable();           // For platform tasks — posted URL
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes for role-based queries
            $table->index(['assigned_to', 'status']);
            $table->index(['project_id', 'type']);
            $table->index(['channel_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
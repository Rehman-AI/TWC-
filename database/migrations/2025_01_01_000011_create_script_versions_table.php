<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('script_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->json('content')->nullable();
            $table->longText('plain_text')->nullable();
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedSmallInteger('estimated_duration')->default(0);
            $table->string('status')->default('draft');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamps();

            $table->unique(['video_project_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('script_versions');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->text('narration_text')->nullable();
            $table->text('visual_prompt')->nullable();
            $table->text('on_screen_text')->nullable();
            $table->decimal('duration_seconds', 5, 2)->default(0);
            $table->string('status')->default('pending');
            $table->text('flow_prompt')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['video_project_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenes');
    }
};
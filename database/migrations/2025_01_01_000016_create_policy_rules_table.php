<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('category', 50);
            $table->string('title');
            $table->text('description');
            $table->string('severity')->default('warn');
            $table->text('check_logic')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_verified_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Policy review results per video
        Schema::create('policy_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_project_id')->constrained()->cascadeOnDelete();
            $table->string('overall_result');
            $table->json('rule_results');
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_reviews');
        Schema::dropIfExists('policy_rules');
    }
};
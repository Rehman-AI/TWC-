<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoMetadata extends Model
{
    use HasFactory;

    protected $table = 'seo_metadata';

    protected $fillable = [
        'video_project_id',
        'seo_title',
        'seo_description',
        'tags',
        'hashtags',
        'thumbnail_text',
        'seo_score',
        'score_breakdown',
        'suggestions',
    ];

    protected function casts(): array
    {
        return [
            'tags'            => 'array',
            'hashtags'        => 'array',
            'seo_score'       => 'integer',
            'score_breakdown' => 'array',
            'suggestions'     => 'array',
        ];
    }

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function getScoreColor(): string
    {
        return match (true) {
            $this->seo_score >= 80 => 'success',
            $this->seo_score >= 60 => 'warning',
            $this->seo_score >= 40 => 'info',
            default                => 'danger',
        };
    }

    public function getScoreLabel(): string
    {
        return match (true) {
            $this->seo_score >= 80 => 'Excellent',
            $this->seo_score >= 60 => 'Good',
            $this->seo_score >= 40 => 'Fair',
            default                => 'Needs Work',
        };
    }
}
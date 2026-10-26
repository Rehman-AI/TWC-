<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_project_id',
        'overall_result',
        'rule_results',
        'notes',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'rule_results' => 'array',
        ];
    }

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPassing(): bool
    {
        return $this->overall_result === 'pass';
    }

    public function isBlocked(): bool
    {
        return $this->overall_result === 'block';
    }

    public function getPassCount(): int
    {
        return collect($this->rule_results ?? [])->where('result', 'pass')->count();
    }

    public function getWarnCount(): int
    {
        return collect($this->rule_results ?? [])->where('result', 'warn')->count();
    }

    public function getBlockCount(): int
    {
        return collect($this->rule_results ?? [])->where('result', 'block')->count();
    }
}
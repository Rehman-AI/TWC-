<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'daily_target',
        'weekly_target',
    ];

    protected function casts(): array
    {
        return [
            'daily_target'  => 'integer',
            'weekly_target' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function dailyCompleted(): int
    {
        return VideoProject::where('assigned_to', $this->user_id)
            ->where('status', 'published')
            ->whereDate('updated_at', today())
            ->count();
    }

    public function weeklyCompleted(): int
    {
        return VideoProject::where('assigned_to', $this->user_id)
            ->where('status', 'published')
            ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
    }

    public function dailyProgress(): float
    {
        if ($this->daily_target === 0) return 100;
        return min(100, round(($this->dailyCompleted() / $this->daily_target) * 100));
    }

    public function weeklyProgress(): float
    {
        if ($this->weekly_target === 0) return 100;
        return min(100, round(($this->weeklyCompleted() / $this->weekly_target) * 100));
    }
}
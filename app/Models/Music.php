<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Music extends Model
{
    use HasFactory;

    protected $table = 'music';

    protected $fillable = [
        'title',
        'artist',
        'genre',
        'mood',
        'bpm',
        'duration_seconds',
        'license_type',
        'drive_file_id',
        'drive_url',
        'local_path',
        'tags',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bpm'              => 'integer',
            'duration_seconds' => 'integer',
            'tags'             => 'array',
            'is_active'        => 'boolean',
        ];
    }

    // -- Relationships -----------------------------------------------

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_music');
    }

    // -- Helpers ------------------------------------------------------

    public function durationFormatted(): string
    {
        $minutes = intdiv($this->duration_seconds, 60);
        $seconds = $this->duration_seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
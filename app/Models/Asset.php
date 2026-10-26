<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AssetType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_project_id',
        'scene_id',
        'type',
        'filename',
        'drive_file_id',
        'drive_url',
        'local_path',
        'mime_type',
        'size_bytes',
        'checksum',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type'       => AssetType::class,
            'size_bytes' => 'integer',
            'metadata'   => 'array',
        ];
    }

    // -- Relationships -----------------------------------------------

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    // -- Helpers ------------------------------------------------------

    public function isOnDrive(): bool
    {
        return ! empty($this->drive_file_id);
    }

    public function hasLocalCopy(): bool
    {
        return ! empty($this->local_path) && file_exists($this->local_path);
    }

    public function humanSize(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    /** The Drive folder this asset should live in */
    public function targetDriveFolder(): string
    {
        return $this->type->driveFolder();
    }
}
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomRole extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'permissions' => 'array',
        'is_active'   => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'custom_role_id');
    }

    /** Check if this role has a specific permission */
    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /** All available permissions (boss sees these as checkboxes) */
    public static function availablePermissions(): array
    {
        return [
            'write_script'      => 'Write Scripts',
            'assign_to_video'   => 'Assign Script to Video Maker',
            'make_video'        => 'Make Videos',
            'upload_youtube'    => 'Upload to YouTube',
            'upload_facebook'   => 'Upload to Facebook',
            'upload_instagram'  => 'Upload to Instagram',
            'upload_tiktok'     => 'Upload to TikTok',
            'upload_snapchat'   => 'Upload to Snapchat',
            'manage_tasks'      => 'Manage & Assign Tasks',
            'view_reports'      => 'View Reports',
            'manage_channels'   => 'Manage Channels',
            'manage_team'       => 'Manage Team Members',
        ];
    }

    /** Get platform-upload permissions this role has */
    public function platformPermissions(): array
    {
        $platforms = [];
        foreach ($this->permissions ?? [] as $perm) {
            if (str_starts_with($perm, 'upload_')) {
                $platforms[] = str_replace('upload_', '', $perm);
            }
        }
        return $platforms;
    }

    /** Is this a script-writing role? */
    public function isScriptWriter(): bool
    {
        return $this->can('write_script');
    }

    /** Is this a video-making role? */
    public function isVideoMaker(): bool
    {
        return $this->can('make_video');
    }

    /** Is this a platform head? */
    public function isPlatformHead(): bool
    {
        foreach ($this->permissions ?? [] as $perm) {
            if (str_starts_with($perm, 'upload_')) return true;
        }
        return false;
    }

    /** Is this a manager-level role? */
    public function isManager(): bool
    {
        return $this->can('manage_tasks');
    }
}
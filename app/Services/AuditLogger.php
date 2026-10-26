<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Record an audit log entry.
     */
    public function log(
        AuditAction $action,
        ?Model $subject = null,
        array $properties = [],
        ?User $user = null,
        ?int $teamId = null,
    ): AuditLog {
        $user ??= auth()->user();

        return AuditLog::create([
            'team_id'      => $teamId ?? $user?->team_id,
            'user_id'      => $user?->id,
            'action'       => $action,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id'   => $subject?->getKey(),
            'properties'   => ! empty($properties) ? $properties : null,
            'ip_address'   => Request::ip(),
            'user_agent'   => Request::userAgent(),
        ]);
    }

    /**
     * Log a model creation.
     */
    public function logCreated(AuditAction $action, Model $subject, array $extra = []): AuditLog
    {
        return $this->log($action, $subject, array_merge([
            'after' => $subject->toArray(),
        ], $extra));
    }

    /**
     * Log a model update with before/after diff.
     */
    public function logUpdated(AuditAction $action, Model $subject, array $extra = []): AuditLog
    {
        return $this->log($action, $subject, array_merge([
            'before' => $subject->getOriginal(),
            'after'  => $subject->getChanges(),
        ], $extra));
    }

    /**
     * Log a model deletion.
     */
    public function logDeleted(AuditAction $action, Model $subject, array $extra = []): AuditLog
    {
        return $this->log($action, $subject, array_merge([
            'before' => $subject->toArray(),
        ], $extra));
    }
}

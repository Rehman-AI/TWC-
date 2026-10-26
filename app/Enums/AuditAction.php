<?php

declare(strict_types=1);

namespace App\Enums;

enum AuditAction: string
{
    case Login              = 'login';
    case Logout             = 'logout';
    case Register           = 'register';
    case TeamCreated        = 'team.created';
    case TeamUpdated        = 'team.updated';
    case TeamDeleted        = 'team.deleted';
    case MemberInvited      = 'member.invited';
    case MemberRemoved      = 'member.removed';
    case RoleChanged        = 'role.changed';
    case ChannelCreated     = 'channel.created';
    case ChannelUpdated     = 'channel.updated';
    case ChannelDeleted     = 'channel.deleted';
    case ChannelAccessGranted = 'channel.access_granted';
    case ChannelAccessRevoked = 'channel.access_revoked';
    case Approve            = 'approve';
    case Publish            = 'publish';
    case TokenAccess        = 'token.access';
    case TokenRefresh       = 'token.refresh';
    case VideoPublished     = 'video.published';
    case VideoApproved      = 'video.approved';
    case ScriptApproved     = 'script.approved';
    case ScriptRejected     = 'script.rejected';
    case PolicyReviewed     = 'policy.reviewed';
    case QaReviewed         = 'qa.reviewed';
    case SeoGenerated       = 'seo.generated';

    public function label(): string
    {
        return str_replace(['.', '_'], ' ', ucfirst($this->value));
    }

    public function isSensitive(): bool
    {
        return in_array($this, [
            self::Publish,
            self::Approve,
            self::VideoPublished,
            self::VideoApproved,
            self::TokenAccess,
            self::TokenRefresh,
            self::MemberRemoved,
            self::ChannelDeleted,
            self::TeamDeleted,
        ]);
    }
}
<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum AssetType: string implements HasLabel, HasIcon
{
    case Script      = 'script';
    case Voiceover   = 'voiceover';
    case Caption     = 'caption';
    case Clip        = 'clip';
    case Music       = 'music';
    case EditPack    = 'edit_pack';
    case FinalExport = 'final_export';
    case Thumbnail   = 'thumbnail';
    case QaReport    = 'qa_report';
    case Seo         = 'seo';

    public function getLabel(): string
    {
        return match ($this) {
            self::Script      => 'Script',
            self::Voiceover   => 'Voiceover',
            self::Caption     => 'Caption',
            self::Clip        => 'Clip',
            self::Music       => 'Music',
            self::EditPack    => 'Edit Pack',
            self::FinalExport => 'Final Export',
            self::Thumbnail   => 'Thumbnail',
            self::QaReport    => 'QA Report',
            self::Seo         => 'SEO',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Script      => 'heroicon-o-document-text',
            self::Voiceover   => 'heroicon-o-microphone',
            self::Caption     => 'heroicon-o-chat-bubble-bottom-center-text',
            self::Clip        => 'heroicon-o-film',
            self::Music       => 'heroicon-o-musical-note',
            self::EditPack    => 'heroicon-o-archive-box',
            self::FinalExport => 'heroicon-o-document-check',
            self::Thumbnail   => 'heroicon-o-photo',
            self::QaReport    => 'heroicon-o-clipboard-document-check',
            self::Seo         => 'heroicon-o-magnifying-glass',
        };
    }

    /** Folder name in Drive structure */
    public function driveFolder(): string
    {
        return match ($this) {
            self::Script      => '01_Script',
            self::Voiceover   => '04_Voiceover',
            self::Caption     => '05_Captions',
            self::Clip        => '03_Flow_Clips',
            self::Music       => '06_Music',
            self::EditPack    => '07_Edit_Pack',
            self::FinalExport => '08_Final_Export',
            self::Thumbnail   => '08_Final_Export',
            self::QaReport    => '09_QA_Reports',
            self::Seo         => '10_SEO',
        };
    }
}
<?php

namespace App\Filament\Resources;

use App\Enums\PlatformEnum;
use App\Filament\Resources\ChannelResource\Pages;
use App\Filament\Resources\ChannelResource\RelationManagers;
use App\Models\Channel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;
    protected static ?string $navigationIcon = 'heroicon-o-tv';
    protected static ?string $navigationGroup = 'Production';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Channel')
                ->columnSpanFull()
                ->tabs([
                    // -- General Tab --
                    Forms\Components\Tabs\Tab::make('General')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                                        $set('slug', Str::slug($state))
                                    ),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                            ]),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('niche')
                                    ->placeholder('e.g. AI Tools, Car Reviews, History')
                                    ->maxLength(255),
                                Forms\Components\Select::make('language')
                                    ->options([
                                        'en' => 'English', 'ur' => 'Urdu', 'ar' => 'Arabic', 'hi' => 'Hindi',
                                        'es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'pt' => 'Portuguese',
                                        'zh' => 'Chinese (Mandarin)', 'ja' => 'Japanese', 'ko' => 'Korean',
                                        'ru' => 'Russian', 'it' => 'Italian', 'nl' => 'Dutch', 'pl' => 'Polish',
                                        'tr' => 'Turkish', 'th' => 'Thai', 'vi' => 'Vietnamese', 'id' => 'Indonesian',
                                        'ms' => 'Malay', 'tl' => 'Filipino/Tagalog', 'bn' => 'Bengali', 'ta' => 'Tamil',
                                        'te' => 'Telugu', 'mr' => 'Marathi', 'gu' => 'Gujarati', 'kn' => 'Kannada',
                                        'ml' => 'Malayalam', 'pa' => 'Punjabi', 'sw' => 'Swahili', 'am' => 'Amharic',
                                        'ha' => 'Hausa', 'yo' => 'Yoruba', 'zu' => 'Zulu', 'af' => 'Afrikaans',
                                        'fa' => 'Persian/Farsi', 'ps' => 'Pashto', 'ku' => 'Kurdish',
                                        'he' => 'Hebrew', 'el' => 'Greek', 'cs' => 'Czech', 'sk' => 'Slovak',
                                        'hu' => 'Hungarian', 'ro' => 'Romanian', 'bg' => 'Bulgarian', 'hr' => 'Croatian',
                                        'sr' => 'Serbian', 'uk' => 'Ukrainian', 'sv' => 'Swedish', 'no' => 'Norwegian',
                                        'da' => 'Danish', 'fi' => 'Finnish', 'et' => 'Estonian', 'lv' => 'Latvian',
                                        'lt' => 'Lithuanian', 'sl' => 'Slovenian', 'sq' => 'Albanian',
                                        'bs' => 'Bosnian', 'mk' => 'Macedonian', 'ka' => 'Georgian',
                                        'hy' => 'Armenian', 'az' => 'Azerbaijani', 'uz' => 'Uzbek', 'kk' => 'Kazakh',
                                        'mn' => 'Mongolian', 'my' => 'Burmese', 'km' => 'Khmer', 'lo' => 'Lao',
                                        'ne' => 'Nepali', 'si' => 'Sinhala', 'ca' => 'Catalan', 'gl' => 'Galician',
                                        'eu' => 'Basque', 'cy' => 'Welsh', 'ga' => 'Irish', 'mt' => 'Maltese',
                                        'la' => 'Latin', 'eo' => 'Esperanto',
                                    ])
                                    ->default('en')
                                    ->searchable(),
                            ]),
                            Forms\Components\Textarea::make('description')
                                ->rows(3)
                                ->columnSpanFull()
                                ->placeholder('Brief description of this channel'),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('video_min_duration')
                                    ->label('Min Video Duration')
                                    ->options(['15 sec'=>'15 sec','30 sec'=>'30 sec','1 min'=>'1 min','2 min'=>'2 min','5 min'=>'5 min','10 min'=>'10 min','30 min'=>'30 min'])
                                    ->default('1 min'),
                                Forms\Components\Select::make('video_max_duration')
                                    ->label('Max Video Duration')
                                    ->options(['1 min'=>'1 min','5 min'=>'5 min','10 min'=>'10 min','30 min'=>'30 min','1 hr'=>'1 hr','2 hr'=>'2 hr'])
                                    ->default('1 hr'),
                            ]),
                            Forms\Components\CheckboxList::make('target_platforms')
                                ->label('Upload videos to these platforms')
                                ->options(fn () => \App\Models\Platform::active()->pluck('name', 'slug')->toArray())
                                ->columns(4)
                                ->columnSpanFull(),
                            Forms\Components\Hidden::make('team_id')
                                ->default(fn () => auth()->user()?->team_id),
                            Forms\Components\Toggle::make('is_active')
                                ->default(true)
                                ->inline(false),
                        ]),

                    // -- Brand Kit Tab --
                    Forms\Components\Tabs\Tab::make('Brand Kit')
                        ->icon('heroicon-o-paint-brush')
                        ->schema([
                            Forms\Components\Section::make('Colors')
                                ->schema([
                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\ColorPicker::make('brand_kit.colors.primary')
                                            ->label('Primary Color'),
                                        Forms\Components\ColorPicker::make('brand_kit.colors.secondary')
                                            ->label('Secondary Color'),
                                        Forms\Components\ColorPicker::make('brand_kit.colors.accent')
                                            ->label('Accent Color'),
                                    ]),
                                ]),
                            Forms\Components\Section::make('Typography')
                                ->schema([
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('brand_kit.fonts.heading')
                                            ->label('Heading Font')
                                            ->placeholder('e.g. Montserrat'),
                                        Forms\Components\TextInput::make('brand_kit.fonts.body')
                                            ->label('Body Font')
                                            ->placeholder('e.g. Inter'),
                                    ]),
                                ]),
                            Forms\Components\Section::make('Assets')
                                ->schema([
                                    Forms\Components\TextInput::make('brand_kit.watermark_url')
                                        ->label('Watermark URL (Drive)')
                                        ->placeholder('Google Drive file URL'),
                                    Forms\Components\TextInput::make('brand_kit.intro_url')
                                        ->label('Intro Video URL (Drive)')
                                        ->placeholder('Google Drive file URL'),
                                    Forms\Components\TextInput::make('brand_kit.outro_url')
                                        ->label('Outro Video URL (Drive)')
                                        ->placeholder('Google Drive file URL'),
                                ]),
                        ]),

                    // -- Style & Content Tab --
                    Forms\Components\Tabs\Tab::make('Style & Content')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\Section::make('Style Guide')
                                ->schema([
                                    Forms\Components\Textarea::make('style_guide.tone')
                                        ->label('Tone of Voice')
                                        ->rows(2)
                                        ->placeholder('e.g. Energetic, casual, informative with humor'),
                                    Forms\Components\Textarea::make('style_guide.hook_style')
                                        ->label('Hook Style')
                                        ->rows(2)
                                        ->placeholder('How should the first 2 seconds grab attention?'),
                                    Forms\Components\Textarea::make('style_guide.cta_style')
                                        ->label('CTA Style')
                                        ->rows(2)
                                        ->placeholder('How should the call-to-action be phrased?'),
                                    Forms\Components\Textarea::make('style_guide.visual_style')
                                        ->label('Visual Style')
                                        ->rows(2)
                                        ->placeholder('Describe the visual look and feel'),
                                ]),
                            Forms\Components\TagsInput::make('banned_words')
                                ->placeholder('Add banned word')
                                ->helperText('Words that must never appear in scripts or captions'),
                        ]),

                    // -- Settings Tab --
                    Forms\Components\Tabs\Tab::make('Settings')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('tts_provider')
                                    ->label('TTS Provider')
                                    ->options([
                                        'edge_tts'    => 'Edge TTS (Free)',
                                        'elevenlabs'  => 'ElevenLabs (Premium)',
                                    ])
                                    ->default('edge_tts'),
                                Forms\Components\Select::make('stock_provider')
                                    ->label('Stock Footage Provider')
                                    ->options([
                                        'pexels'  => 'Pexels',
                                        'pixabay' => 'Pixabay',
                                    ])
                                    ->default('pexels'),
                            ]),
                            Forms\Components\Toggle::make('approval_required')
                                ->label('Require approval before publishing')
                                ->default(true)
                                ->helperText('If enabled, a Manager or Owner must approve before any video is published')
                                ->inline(false),
                            Forms\Components\Section::make('Posting Schedule')
                                ->schema([
                                    Forms\Components\KeyValue::make('posting_schedule')
                                        ->keyLabel('Day')
                                        ->valueLabel('Time (HH:MM)')
                                        ->addActionLabel('Add Schedule')
                                        ->keyPlaceholder('e.g. Monday')
                                        ->valuePlaceholder('e.g. 14:00'),
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('niche')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('language')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => strtoupper($state)),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('video_projects_count')
                    ->counts('videoProjects')
                    ->label('Videos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('platform_accounts_count')
                    ->counts('platformAccounts')
                    ->label('Accounts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tts_provider')
                    ->label('TTS')
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('language')
                    ->options([
                        'en' => 'English',
                        'ur' => 'Urdu',
                        'ar' => 'Arabic',
                        'es' => 'Spanish',
                        'ja' => 'Japanese',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PlatformAccountsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannel::route('/create'),
            'edit'   => Pages\EditChannel::route('/{record}/edit'),
        ];
    }
}
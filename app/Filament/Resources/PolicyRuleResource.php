<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PolicyRuleResource\Pages;
use App\Models\PolicyRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PolicyRuleResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->role === \App\Enums\TeamRole::Owner || auth()->user()?->role === \App\Enums\TeamRole::Manager;
    }

    protected static ?string $model = PolicyRule::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Quality';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Policy Rules';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Rule Definition')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('LICENSED_FOOTAGE'),
                        Forms\Components\Select::make('category')
                            ->options([
                                'content'    => 'Content',
                                'legal'      => 'Legal',
                                'platform'   => 'Platform Rules',
                                'brand'      => 'Brand Safety',
                                'disclosure' => 'Disclosure',
                            ])
                            ->required(),
                    ]),
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('severity')
                            ->options([
                                'block' => 'BLOCK (Stops publishing)',
                                'warn'  => 'WARN (Flag for review)',
                            ])
                            ->default('warn')
                            ->required(),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->inline(false),
                    ]),
                ]),
            Forms\Components\Section::make('Verification')
                ->schema([
                    Forms\Components\DateTimePicker::make('last_verified_at')
                        ->label('Last Verified'),
                    Forms\Components\TextInput::make('verified_by')
                        ->label('Verified By'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->weight('bold')
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'legal'      => 'danger',
                        'platform'   => 'warning',
                        'content'    => 'info',
                        'brand'      => 'primary',
                        'disclosure' => 'gray',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('severity')
                    ->badge()
                    ->color(fn (string $state) => $state === 'block' ? 'danger' : 'warning')
                    ->formatStateUsing(fn (string $state) => strtoupper($state)),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_verified_at')
                    ->label('Last Verified')
                    ->dateTime()
                    ->placeholder('Never'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'content'    => 'Content',
                        'legal'      => 'Legal',
                        'platform'   => 'Platform Rules',
                        'brand'      => 'Brand Safety',
                        'disclosure' => 'Disclosure',
                    ]),
                Tables\Filters\SelectFilter::make('severity')
                    ->options(['block' => 'BLOCK', 'warn' => 'WARN']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verify')
                    ->label('Mark Verified')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'last_verified_at' => now(),
                        'verified_by'      => auth()->user()?->name,
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPolicyRules::route('/'),
            'create' => Pages\CreatePolicyRule::route('/create'),
            'edit'   => Pages\EditPolicyRule::route('/{record}/edit'),
        ];
    }
}
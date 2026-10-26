<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomRoleResource\Pages;
use App\Models\CustomRole;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomRoleResource extends Resource
{
    protected static ?string $model = CustomRole::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Roles & Permissions';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 5;
    protected static ?string $modelLabel = 'Role';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->role === 'owner' || $user->role?->value === 'owner');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Role Info')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('e.g. YouTube Head'),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->placeholder('e.g. youtube_head')
                        ->helperText('Unique identifier, lowercase with underscores'),
                    Forms\Components\Select::make('color')
                        ->options([
                            'danger'  => 'Red',
                            'primary' => 'Pink',
                            'info'    => 'Blue',
                            'success' => 'Green',
                            'warning' => 'Orange',
                            'gray'    => 'Gray',
                        ])
                        ->default('gray'),
                    Forms\Components\Toggle::make('is_active')->default(true),
                ]),

            Forms\Components\Section::make('What can this role do?')
                ->description('Select all permissions for this role')
                ->schema([
                    Forms\Components\CheckboxList::make('permissions')
                        ->label('')
                        ->options(CustomRole::availablePermissions())
                        ->columns(2)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->fontFamily('mono')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('permissions')
                    ->label('Permissions')
                    ->formatStateUsing(function ($state) {
                        if (!is_array($state)) return '';
                        $labels = CustomRole::availablePermissions();
                        return collect($state)->map(fn ($p) => $labels[$p] ?? $p)->implode(', ');
                    })
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Members'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit'   => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlatformResource\Pages;
use App\Models\Platform;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlatformResource extends Resource
{
    protected static ?string $model = Platform::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'Platforms';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return auth()->user()?->role?->isOwnerOrManager() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(100)
                ->placeholder('e.g. Snapchat'),
            Forms\Components\TextInput::make('slug')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->placeholder('e.g. snapchat'),
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
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('slug')->fontFamily('mono'),
                Tables\Columns\TextColumn::make('color')->badge()->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPlatforms::route('/'),
            'create' => Pages\CreatePlatform::route('/create'),
            'edit'   => Pages\EditPlatform::route('/{record}/edit'),
        ];
    }
}
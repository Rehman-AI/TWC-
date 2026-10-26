<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\CustomRole;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Team Members';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->role === \App\Enums\TeamRole::Owner;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Member Info')
                ->schema([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('email')->email()->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->required(fn ($operation) => $operation === 'create')
                        ->dehydrated(fn ($state) => filled($state))
                        ->helperText('Leave blank to keep current password'),
                ]),

            Forms\Components\Section::make('Role Assignment')
                ->schema([
                    Forms\Components\Select::make('custom_role_id')
                        ->label('Role')
                        ->options(CustomRole::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->helperText('Choose the role for this team member. Roles are created in Settings > Roles & Permissions'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(User::query()->where('role', '!=', 'owner')->with('customRole'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('customRole.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn ($record) => $record->customRole?->color ?? 'gray'),
                Tables\Columns\TextColumn::make('assigned_tasks_count')
                    ->counts('assignedTasks')
                    ->label('Tasks'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
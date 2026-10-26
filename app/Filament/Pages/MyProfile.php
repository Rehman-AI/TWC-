<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MyProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'My Profile';
    protected static ?string $title = 'My Profile';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.my-profile';

    public ?array $profileData = [];
    public ?array $passwordData = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->profileForm->fill([
            'name'  => $user->name,
            'email' => $user->email,
        ]);
    }

    protected function getForms(): array
    {
        return [
            'profileForm',
            'passwordForm',
        ];
    }

    public function profileForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Info')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->disabled(),
                    ]),
            ])
            ->statePath('profileData');
    }

    public function passwordForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Change Password')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->password()
                            ->required()
                            ->currentPassword()
                            ->label('Current Password'),
                        Forms\Components\TextInput::make('new_password')
                            ->password()
                            ->required()
                            ->rule(Password::min(6))
                            ->label('New Password'),
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->password()
                            ->required()
                            ->same('new_password')
                            ->label('Confirm New Password'),
                    ]),
            ])
            ->statePath('passwordData');
    }

    public function updateProfile(): void
    {
        $data = $this->profileForm->getState();
        auth()->user()->update(['name' => $data['name']]);
        Notification::make()->title('Profile updated!')->success()->send();
    }

    public function updatePassword(): void
    {
        $data = $this->passwordForm->getState();
        auth()->user()->update([
            'password' => Hash::make($data['new_password']),
        ]);
        $this->passwordForm->fill();
        Notification::make()->title('Password changed!')->success()->send();
    }
}
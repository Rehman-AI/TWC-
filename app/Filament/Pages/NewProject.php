<?php

namespace App\Filament\Pages;

use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Enums\TeamRole;
use App\Filament\Resources\TaskResource;
use App\Models\Channel;
use App\Models\Platform;
use App\Models\Task;
use App\Models\User;
use App\Models\VideoProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class NewProject extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?string $navigationLabel = 'New Project';
    protected static ?string $title = 'Create New Project';
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.pages.new-project';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === \App\Enums\TeamRole::Owner || auth()->user()?->role === \App\Enums\TeamRole::Manager;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        // Get active platforms dynamically
        $platformOptions = Platform::active()->pluck('name', 'slug')->toArray();
        $platformDefaults = array_keys($platformOptions);

        return $form
            ->schema([
                Forms\Components\Section::make('Project Info')
                    ->schema([
                        Forms\Components\Select::make('channel_id')
                            ->label('Channel')
                            ->options(Channel::pluck('name', 'id'))
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('Video Title / Topic')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Top 5 AI Tools for Designers'),
                        Forms\Components\Textarea::make('description')
                            ->label('Brief / Notes')
                            ->rows(3)
                            ->placeholder('What should the video be about?'),
                    ]),

                Forms\Components\Section::make('Script Assignment')
                    ->schema([
                        Forms\Components\Select::make('scriptwriter_id')
                            ->label('Assign Script To')
                            ->options(fn () => User::where('role', TeamRole::Scriptwriter->value)->pluck('name', 'id'))
                            ->required(),
                        Forms\Components\DateTimePicker::make('script_deadline')
                            ->label('Script Deadline')
                            ->default(today()->endOfDay())
                            ->required(),
                    ]),

                Forms\Components\Section::make('Platform Settings')
                    ->description('Choose which platforms this video will be posted to after the video is done')
                    ->schema([
                        Forms\Components\CheckboxList::make('platforms')
                            ->label('')
                            ->options($platformOptions)
                            ->default($platformDefaults)
                            ->columns(4),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $project = VideoProject::create([
            'title'      => $data['title'],
            'slug'       => \Illuminate\Support\Str::slug($data['title']) . '-' . \Illuminate\Support\Str::random(4),
            'channel_id' => $data['channel_id'],
            'status'     => 'idea',
            'notes'      => $data['description'] ?? null,
            'metadata'   => ['platforms' => $data['platforms']],
        ]);

        Task::create([
            'project_id'  => $project->id,
            'channel_id'  => $data['channel_id'],
            'type'        => TaskType::Script->value,
            'status'      => TaskStatus::Pending->value,
            'assigned_to' => $data['scriptwriter_id'],
            'assigned_by' => auth()->id(),
            'deadline'    => $data['script_deadline'],
            'notes'       => $data['description'] ?? null,
        ]);

        Notification::make()
            ->title('Project created & script assigned!')
            ->body($data['title'])
            ->success()
            ->send();

        $this->form->fill();
        $this->redirect(TaskResource::getUrl());
    }
}
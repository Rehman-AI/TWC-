<x-filament-panels::page>
    <style>
        .kanban-board {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            min-height: 70vh;
        }
        .kanban-column {
            flex: 0 0 280px;
            background: rgba(24, 24, 27, 0.6);
            border: 1px solid rgba(63, 63, 70, 0.5);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(12px);
        }
        .kanban-column-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(63, 63, 70, 0.5);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 12px 12px 0 0;
        }
        .kanban-column-title {
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .kanban-column-count {
            background: rgba(63, 63, 70, 0.5);
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .kanban-column-body {
            padding: 0.75rem;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .kanban-card {
            background: rgba(39, 39, 42, 0.8);
            border: 1px solid rgba(63, 63, 70, 0.4);
            border-radius: 8px;
            padding: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .kanban-card:hover {
            border-color: #e8227a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(232, 34, 122, 0.15);
        }
        .kanban-card-title {
            font-weight: 600;
            font-size: 0.8125rem;
            margin-bottom: 0.375rem;
            color: #fafafa;
        }
        .kanban-card-channel {
            font-size: 0.6875rem;
            color: #a1a1aa;
            margin-bottom: 0.375rem;
        }
        .kanban-card-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .kanban-badge {
            font-size: 0.625rem;
            padding: 0.125rem 0.375rem;
            border-radius: 4px;
            font-weight: 500;
        }
        .badge-duration { background: rgba(14, 165, 233, 0.15); color: #38bdf8; }
        .badge-priority-urgent { background: rgba(239, 68, 68, 0.15); color: #f87171; }
        .badge-priority-high { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .badge-priority-normal { background: rgba(14, 165, 233, 0.15); color: #38bdf8; }
        .badge-priority-low { background: rgba(113, 113, 122, 0.15); color: #a1a1aa; }
        .kanban-card-assignee {
            font-size: 0.6875rem;
            color: #71717a;
            margin-top: 0.375rem;
        }
        .kanban-empty {
            text-align: center;
            padding: 2rem 1rem;
            color: #52525b;
            font-size: 0.8125rem;
        }
        .kanban-card-actions {
            display: flex;
            gap: 0.25rem;
            margin-top: 0.5rem;
        }
        .kanban-card-actions button {
            font-size: 0.625rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            border: 1px solid rgba(63, 63, 70, 0.5);
            background: transparent;
            color: #a1a1aa;
            cursor: pointer;
            transition: all 0.15s;
        }
        .kanban-card-actions button:hover {
            border-color: #e8227a;
            color: #e8227a;
        }
    </style>

    <div class="kanban-board">
        @foreach($this->getColumns() as $column)
            @php
                $projects = $this->getProjectsForStatus($column['status']);
            @endphp
            <div class="kanban-column">
                <div class="kanban-column-header" style="border-left: 3px solid {{ $column['color'] }};">
                    <span class="kanban-column-title" style="color: {{ $column['color'] }};">
                        {{ $column['label'] }}
                    </span>
                    <span class="kanban-column-count">{{ $projects->count() }}</span>
                </div>
                <div class="kanban-column-body">
                    @forelse($projects as $project)
                        <div class="kanban-card" wire:key="project-{{ $project->id }}">
                            <div class="kanban-card-title">{{ $project->title }}</div>
                            <div class="kanban-card-channel">
                                {{ $project->channel?->name ?? 'No Channel' }}
                            </div>
                            <div class="kanban-card-meta">
                                <span class="kanban-badge badge-duration">{{ $project->target_duration }}s</span>
                                <span class="kanban-badge badge-priority-{{ $project->priority?->value ?? 'normal' }}">
                                    {{ $project->priority?->getLabel() ?? 'Normal' }}
                                </span>
                                <span class="kanban-badge" style="background: rgba(232,34,122,0.15); color: #e8227a;">
                                    {{ strtoupper($project->language) }}
                                </span>
                            </div>
                            @if($project->assignee)
                                <div class="kanban-card-assignee">
                                    &#x1F464; {{ $project->assignee->name }}
                                </div>
                            @endif
                            @if($project->due_date)
                                <div class="kanban-card-assignee" style="{{ $project->isOverdue() ? 'color: #f87171;' : '' }}">
                                    &#x1F4C5; {{ $project->due_date->format('M j') }}
                                    @if($project->isOverdue()) (Overdue) @endif
                                </div>
                            @endif
                            <div class="kanban-card-actions">
                                <a href="{{ route('filament.admin.resources.video-projects.view', $project) }}"
                                   style="font-size: 0.625rem; padding: 0.2rem 0.5rem; border-radius: 4px; border: 1px solid rgba(63,63,70,0.5); color: #a1a1aa; text-decoration: none;">
                                    View
                                </a>
                                @foreach($column['status']->allowedTransitions() as $next)
                                    @if($next->isScriptPhase())
                                        <button wire:click="moveProject({{ $project->id }}, '{{ $next->value }}')"
                                                type="button">
                                            &rarr; {{ $next->getLabel() }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="kanban-empty">No items</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
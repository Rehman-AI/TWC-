<x-filament-panels::page>
    <style>
        .pipeline-board {
            display: flex;
            gap: 0.75rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            min-height: 65vh;
        }
        .pipeline-col {
            flex: 0 0 220px;
            background: rgba(24, 24, 27, 0.6);
            border: 1px solid rgba(63, 63, 70, 0.4);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
        }
        .pipeline-col-header {
            padding: 0.625rem 0.75rem;
            border-bottom: 1px solid rgba(63, 63, 70, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.375rem;
        }
        .pipeline-col-title {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pipeline-col-count {
            background: rgba(63, 63, 70, 0.4);
            padding: 0.0625rem 0.375rem;
            border-radius: 9999px;
            font-size: 0.625rem;
            font-weight: 600;
            flex-shrink: 0;
        }
        .pipeline-col-body {
            padding: 0.5rem;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }
        .pipeline-card {
            background: rgba(39, 39, 42, 0.7);
            border: 1px solid rgba(63, 63, 70, 0.3);
            border-radius: 6px;
            padding: 0.5rem;
            transition: all 0.15s;
        }
        .pipeline-card:hover {
            border-color: #e8227a;
            box-shadow: 0 2px 8px rgba(232, 34, 122, 0.12);
        }
        .pipeline-card-title {
            font-size: 0.6875rem;
            font-weight: 600;
            color: #fafafa;
            margin-bottom: 0.25rem;
            line-height: 1.3;
        }
        .pipeline-card-channel {
            font-size: 0.5625rem;
            color: #71717a;
        }
        .pipeline-card-assignee {
            font-size: 0.5625rem;
            color: #a1a1aa;
            margin-top: 0.25rem;
        }
        .pipeline-card a {
            font-size: 0.5625rem;
            color: #e8227a;
            text-decoration: none;
            display: inline-block;
            margin-top: 0.25rem;
        }
        .pipeline-card a:hover { text-decoration: underline; }
        .pipeline-empty {
            text-align: center;
            padding: 1.5rem 0.5rem;
            color: #3f3f46;
            font-size: 0.6875rem;
        }
    </style>

    <div class="pipeline-board">
        @foreach($this->getColumns() as $column)
            @php
                $projects = $this->getProjectsForStatus($column['status']);
                $colorMap = [
                    'gray' => '#71717a', 'info' => '#0ea5e9', 'warning' => '#f59e0b',
                    'success' => '#10b981', 'primary' => '#e8227a', 'danger' => '#ef4444',
                ];
                $color = $colorMap[$column['color']] ?? '#71717a';
            @endphp
            <div class="pipeline-col">
                <div class="pipeline-col-header" style="border-left: 3px solid {{ $color }};">
                    <span class="pipeline-col-title" style="color: {{ $color }};" title="{{ $column['label'] }}">
                        {{ $column['label'] }}
                    </span>
                    <span class="pipeline-col-count">{{ $projects->count() }}</span>
                </div>
                <div class="pipeline-col-body">
                    @forelse($projects as $project)
                        <div class="pipeline-card">
                            <div class="pipeline-card-title">{{ Str::limit($project->title, 35) }}</div>
                            <div class="pipeline-card-channel">{{ $project->channel?->name }}</div>
                            @if($project->assignee)
                                <div class="pipeline-card-assignee">{{ $project->assignee->name }}</div>
                            @endif
                            <a href="{{ route('filament.admin.resources.video-projects.view', $project) }}">View &rarr;</a>
                        </div>
                    @empty
                        <div class="pipeline-empty">&mdash;</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
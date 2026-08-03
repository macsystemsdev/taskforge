<?php

namespace App\Filament\Reporting\Widgets\Project;

use App\Data\Reporting\Project\ProjectHealthData;
use App\Data\Reporting\Project\ProjectReportFilterData;
use App\Domain\Projects\Enums\ProjectHealthStatus;
use App\Services\Reporting\Cache\ProjectReportingCacheService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class ProjectHealthTableWidget extends TableWidget
{
    /**
     * Widget heading displayed above the table.
     */
    protected static ?string $heading = 'Project Health';

    /**
     * Refresh reporting data periodically.
     *
     * Reporting data is cached independently, therefore polling only
     * refreshes the widget rather than recalculating every request.
     */
    protected static ?string $pollingInterval = '2m';

    /**
     * Display the widget across the full page width.
     */
    protected int|string|array $columnSpan = 'full';
    protected static ?string $slug = 'reporting/projects-health';

    protected ProjectReportingCacheService $reporting;

    public function boot(ProjectReportingCacheService $reporting): void
    {
        $this->reporting = $reporting;
    }

    /**
     * Reporting scope.
     *
     * The widget never decides business scope. For now the owner dashboard
     * requests platform-wide reporting by leaving all filters null.
     *
     * Future reporting pages may override this with organization,
     * workspace or team specific filters.
     */
    protected function filters(): ProjectReportFilterData
    {
        return new ProjectReportFilterData(
            organizationId: null,
            workspaceId: null,
            teamId: null,
        );
    }

    /**
     * Retrieve reporting data.
     *
     * Business logic remains inside the reporting layer.
     *
     * @return Collection<int, ProjectHealthData>
     */
    protected function reportData(): Collection
    {
        return $this->reporting->health(
            $this->filters(),
        );
    }

    /**
     * Define the table structure.
     */
    public function table(Table $table): Table
    {
        return $table
            ->records(function (): Collection {
                $data = $this->reportData()->map(fn(ProjectHealthData $dto) => [
                    'project_id' => $dto->projectId,
                    'project_name' => $dto->projectName,
                    'project_slug' => $dto->projectSlug,
                    'status' => $dto->status,
                    'completion' => $dto->completionPercentage,
                    'total_tasks' => $dto->totalTasks,
                    'completed_tasks' => $dto->completedTasks,
                    'in_progress_tasks' => $dto->inProgressTasks,
                    'blocked_tasks' => $dto->blockedTasks,
                    'overdue_tasks' => $dto->overdueTasks,
                    'due_soon_tasks' => $dto->dueSoonTasks,
                    'reason' => $dto->reason,
                ]);

                // Apply filters manually
                if ($status = request()->input('tableFilters.status.value')) {
                    $data = $data->filter(
                        fn($item) =>
                        $item['status']->value === $status
                    );
                }

                return $data;
            })
            ->columns([
                // Project Identity
                Tables\Columns\TextColumn::make('project_name')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn($record) => $record['project_slug'] ?? null),

                // Health Status
                Tables\Columns\TextColumn::make('status')
                    ->label('Health')
                    ->badge()
                    ->color(fn(ProjectHealthStatus $state): string => match ($state) {
                        ProjectHealthStatus::HEALTHY => 'success',
                        ProjectHealthStatus::AT_RISK => 'warning',
                        ProjectHealthStatus::CRITICAL => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(ProjectHealthStatus $state): string => match ($state) {
                        ProjectHealthStatus::HEALTHY => 'heroicon-o-check-circle',
                        ProjectHealthStatus::AT_RISK => 'heroicon-o-exclamation-triangle',
                        ProjectHealthStatus::CRITICAL => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn(ProjectHealthStatus $state): string => $state->label() ?? ucfirst($state->value)),

                // Completion
                Tables\Columns\TextColumn::make('completion')
                    ->label('Progress')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),

                // Task Breakdown
                Tables\Columns\TextColumn::make('total_tasks')
                    ->label('Total Tasks')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_tasks')
                    ->label('Completed')
                    ->numeric()
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('in_progress_tasks')
                    ->label('In Progress')
                    ->numeric()
                    ->sortable()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('blocked_tasks')
                    ->label('Blocked')
                    ->numeric()
                    ->sortable()
                    ->color('danger'),

                // Task Alerts
                Tables\Columns\TextColumn::make('overdue_tasks')
                    ->label('Overdue')
                    ->numeric()
                    ->sortable()
                    ->color('danger')
                    ->badge()
                    ->visible(fn($record) => ($record['overdue_tasks'] ?? 0) > 0),

                Tables\Columns\TextColumn::make('due_soon_tasks')
                    ->label('Due Soon')
                    ->numeric()
                    ->sortable()
                    ->color('warning')
                    ->badge()
                    ->visible(fn($record) => ($record['due_soon_tasks'] ?? 0) > 0),

                // Reason (shown only when status is not healthy)
                Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(50)
                    ->tooltip(fn($record) => $record['reason'] ?? null)
                    ->visible(
                        fn($record) =>
                        isset($record['reason']) &&
                            $record['status'] !== ProjectHealthStatus::HEALTHY
                    )
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Health Status')
                    ->options(
                        collect(ProjectHealthStatus::cases())
                            ->mapWithKeys(fn($case) => [
                                $case->value => $case->label() ?? ucfirst($case->value)
                            ])
                            ->toArray()
                    )
                    // ✅ This tells Filament NOT to modify the query
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['value']) return null;
                        return 'Status: ' . $data['value'];
                    }),
            ])
            ->defaultSort('completion', 'asc') // Show struggling projects first
            ->paginated([10, 25, 50])
            ->striped();
    }
}

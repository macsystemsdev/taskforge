<?php

namespace App\Filament\Reporting\Widgets\Project;

use App\Data\Reporting\Project\ProjectReportFilterData;
use App\Services\Reporting\Cache\ProjectReportingCacheService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProjectOverviewWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '2m';
    protected static ?string $slug = 'reporting/projects-overview';

    protected function getStats(): array
    {
        $filters = new ProjectReportFilterData(
            organizationId: null,
            workspaceId: null,
            teamId: null,
        );


        $overview = app(ProjectReportingCacheService::class)
            ->overview($filters);


        return [

            Stat::make(
                'Total Projects',
                $overview['total_projects']
            )
                ->description('Projects tracked')
                ->icon('heroicon-o-folder')
                ->color('primary'),


            Stat::make(
                'Healthy Projects',
                $overview['healthy_projects']
            )
                ->description('Operating normally')
                ->icon('heroicon-o-check-circle')
                ->color('success'),


            Stat::make(
                'At Risk Projects',
                $overview['at_risk_projects']
            )
                ->description('Require attention')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),


            Stat::make(
                'Critical Projects',
                $overview['critical_projects']
            )
                ->description('Immediate attention required')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

        ];
    }
}
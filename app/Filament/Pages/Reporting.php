<?php

namespace App\Filament\Pages;

use App\Filament\Reporting\Widgets\Project\ProjectHealthTableWidget;
use App\Filament\Reporting\Widgets\Project\ProjectOverviewWidget;
use App\Filament\Reporting\Widgets\Team\TeamOverviewWidget;
use App\Filament\Reporting\Widgets\Team\TeamProductivityTableWidget;
use App\Filament\Widgets\InfrastructureWidget;
use App\Filament\Widgets\OrganizationHealthTable;
use App\Filament\Widgets\RevenueOverview;
use App\Filament\Widgets\UsageStatsWidget;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class Reporting extends Page
{
    /**
     * The Filament view rendered for this page.
     */
    protected string $view = 'filament.pages.reporting';

    /**
     * Navigation.
     */
    protected static ?string $navigationLabel = 'Reporting';

    protected static string|UnitEnum|null $navigationGroup = 'Analytics';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'reporting';

    /**
     * Page title.
     */
    protected ?string $heading = 'Reporting & Analytics';

    protected ?string $subheading = 'Historical reporting and operational analytics across the platform.';

    /**
     * Widgets displayed above the page content.
     */
    protected function getHeaderWidgets(): array
    {
        return [

            // Projects
            ProjectOverviewWidget::class,
            ProjectHealthTableWidget::class,

            // Teams
            TeamOverviewWidget::class,
            TeamProductivityTableWidget::class,

            // // Organizations
            // OrganizationOverviewWidget::class,
            // OrganizationHealthTable::class,

            // // Platform
            // RevenueOverview::class,
            // UsageStatsWidget::class,
            // InfrastructureWidget::class,

        ];
    }
}

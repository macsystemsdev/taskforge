<x-filament::page>
    <div class="space-y-10">
        <!-- Project Section -->
        <section>
            <div class="mb-6">
                <h2 class="text-xl font-bold text-indigo-600 dark:text-indigo-400 tracking-tight">Project Analytics</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Overview and health of all projects</p>
            </div>
            
            <div class="space-y-8">
                @livewire(\App\Filament\Reporting\Widgets\Project\ProjectOverviewWidget::class)
                
                @livewire(\App\Filament\Reporting\Widgets\Project\ProjectHealthTableWidget::class)
            </div>
        </section>

        <!-- Team Section -->
        <section class="mt-8">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-indigo-600 dark:text-indigo-400 tracking-tight">Team Analytics</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Productivity and performance metrics</p>
            </div>
            
            <div class="space-y-8">
                @livewire(\App\Filament\Reporting\Widgets\Team\TeamOverviewWidget::class)
                
                @livewire(\App\Filament\Reporting\Widgets\Team\TeamProductivityTableWidget::class)
            </div>
        </section>
    </div>
</x-filament::page>

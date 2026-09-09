<x-filament::page>
    <div style="padding-top: 2rem; padding-bottom: 2rem;">

        <!-- Project Section -->
        <section>
            <div style="margin-bottom: 3rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="display: flex; height: 2.5rem; width: 2.5rem; align-items: center; justify-content: center; border-radius: 0.75rem; background-color: #e0e7ff;">
                        <x-filament::icon icon="heroicon-o-folder" style="height: 1.25rem; width: 1.25rem; color: #4f46e5;" />
                    </div>
                    <div>
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: #4f46e5; margin: 0;">Project Analytics</h2>
                        <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.125rem;">Overview and health of all projects</p>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 5rem;">
                @livewire(\App\Filament\Reporting\Widgets\Project\ProjectOverviewWidget::class)
            </div>

            <div style="margin-top: 5rem; margin-bottom: 5rem;">
                @livewire(\App\Filament\Reporting\Widgets\Project\ProjectHealthTableWidget::class)
            </div>
        </section>

        <!-- Team Section -->
        <section style="margin-top: 7rem;">
            <div style="margin-bottom: 3rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="display: flex; height: 2.5rem; width: 2.5rem; align-items: center; justify-content: center; border-radius: 0.75rem; background-color: #e0e7ff;">
                        <x-filament::icon icon="heroicon-o-user-group" style="height: 1.25rem; width: 1.25rem; color: #4f46e5;" />
                    </div>
                    <div>
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: #4f46e5; margin: 0;">Team Analytics</h2>
                        <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.125rem;">Productivity and performance metrics</p>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 5rem;">
                @livewire(\App\Filament\Reporting\Widgets\Team\TeamOverviewWidget::class)
            </div>

            <div style="margin-top: 5rem; margin-bottom: 5rem;">
                @livewire(\App\Filament\Reporting\Widgets\Team\TeamProductivityTableWidget::class)
            </div>
        </section>

    </div>
</x-filament::page>

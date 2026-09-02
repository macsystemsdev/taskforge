<?php

namespace App\Providers;

use App\Contracts\Billing\PaymentGateway;
use App\Infrastructure\Billing\StripePaymentGateway;
use App\Models\Organization;
use App\Models\Project;
use App\Models\StoredFile;
use App\Models\Task;
use App\Models\Team;
use App\Models\Workspace;
use App\Policies\OrganizationPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\StoredFilePolicy;
use App\Policies\TaskPolicy;
use App\Policies\TeamPolicy;
use App\Policies\WorkspacePolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->bind(
            PaymentGateway::class,
            StripePaymentGateway::class,
        );
    }

    public function boot(): void
    {
        $this->configureDefaults();

        // Register Blade components
        Blade::component('layouts-app', \App\View\Components\Layouts\App::class);

        // Use Expose
        if (str_contains(request()->getHttpHost(), 'sharedwithexpose.com')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(Workspace::class, WorkspacePolicy::class);
        Gate::policy(StoredFile::class, StoredFilePolicy::class);
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(app()->isProduction());

        Password::defaults(
            fn() => Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->when(
                    app()->isProduction(),
                    fn(Password $rule) => $rule->uncompromised()
                ),
        );
    }
}

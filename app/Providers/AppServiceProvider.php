<?php

namespace App\Providers;

use App\Contracts\Billing\PaymentGateway;
use App\Infrastructure\Billing\StripePaymentGateway;
use App\Models\Project;
use App\Models\Task;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
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

        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
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

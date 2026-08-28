<?php

namespace Tests;

use App\Domain\Billing\BillingInterval;
use App\Domain\Billing\SubscriptionPlanStatus;
use App\Domain\Organizations\Enums\OrganizationRole;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        View::share('errors', session('errors', new ViewErrorBag()));
        
        // Disable ALL middleware for tests
        $this->withoutMiddleware(AppHttpMiddlewareVerifyCsrfToken::class);
    }
    
    protected function skipUnlessFortifyHas($feature): void
    {
        if (!Features::enabled($feature)) {
            $this->markTestSkipped('Fortify feature not enabled.');
        }
    }
    
    protected function createBillingPlans(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0,
                'currency' => 'USD',
                'billing_interval' => BillingInterval::NONE,
                'status' => SubscriptionPlanStatus::ACTIVE,
                'max_workspaces' => 1,
                'max_projects' => 5,
                'max_members' => 5,
            ],
            [
                'name' => 'Pro Monthly',
                'slug' => 'pro-monthly',
                'price' => 19.99,
                'currency' => 'USD',
                'billing_interval' => BillingInterval::MONTHLY,
                'status' => SubscriptionPlanStatus::ACTIVE,
                'max_workspaces' => 5,
                'max_projects' => 20,
                'max_members' => 10,
            ],
            [
                'name' => 'Team Yearly',
                'slug' => 'team-yearly',
                'price' => 199.99,
                'currency' => 'USD',
                'billing_interval' => BillingInterval::YEARLY,
                'status' => SubscriptionPlanStatus::ACTIVE,
                'max_workspaces' => 10,
                'max_projects' => 50,
                'max_members' => 25,
            ],
        ];
        
        foreach ($plans as $plan) {
            \App\Models\SubscriptionPlan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
    
    protected function createOrganizationWithOwner(?\App\Models\User $owner = null): array
    {
        $owner = $owner ?? \App\Models\User::factory()->create();
        
        $organization = \App\Models\Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Test Org',
            'slug' => 'test-org-' . uniqid(),
            'subscription_plan' => 'free',
            'subscription_status' => 'active',
        ]);
        
        $organization->members()->attach($owner->id, [
            'role' => OrganizationRole::OWNER->value,
        ]);
        
        $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
        \App\Models\Subscription::create([
            'organization_id' => $organization->id,
            'subscription_plan_id' => $freePlan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);
        
        return [$organization, $owner];
    }
    
    protected function createOrganizationWithRoles(): array
    {
        [$organization, $owner] = $this->createOrganizationWithOwner();
        
        $admin = \App\Models\User::factory()->create();
        $member = \App\Models\User::factory()->create();
        
        $organization->members()->attach($admin->id, ['role' => OrganizationRole::ADMIN->value]);
        $organization->members()->attach($member->id, ['role' => OrganizationRole::MEMBER->value]);
        
        return [$organization, $owner, $admin, $member];
    }
}

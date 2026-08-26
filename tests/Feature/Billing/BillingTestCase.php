<?php

namespace Tests\Feature\Billing;

use App\Domain\Billing\BillingInterval;
use App\Domain\Billing\SubscriptionPlanStatus;
use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class BillingTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createPlans();
    }

    protected function createPlans(): void
    {
        SubscriptionPlan::create([
            'name' => 'Free',
            'slug' => 'free',
            'price' => 0,
            'currency' => 'USD',
            'billing_interval' => BillingInterval::NONE,
            'status' => SubscriptionPlanStatus::ACTIVE,
            'max_workspaces' => 1,
            'max_projects' => 5,
            'max_members' => 5,
        ]);

        SubscriptionPlan::create([
            'name' => 'Pro Monthly',
            'slug' => 'pro-monthly',
            'price' => 19.99,
            'currency' => 'USD',
            'billing_interval' => BillingInterval::MONTHLY,
            'status' => SubscriptionPlanStatus::ACTIVE,
            'max_workspaces' => 5,
            'max_projects' => 20,
            'max_members' => 10,
        ]);

        SubscriptionPlan::create([
            'name' => 'Team Yearly',
            'slug' => 'team-yearly',
            'price' => 199.99,
            'currency' => 'USD',
            'billing_interval' => BillingInterval::YEARLY,
            'status' => SubscriptionPlanStatus::ACTIVE,
            'max_workspaces' => 10,
            'max_projects' => 50,
            'max_members' => 25,
        ]);

        SubscriptionPlan::create([
            'name' => 'Retired Plan',
            'slug' => 'retired',
            'price' => 29.99,
            'currency' => 'USD',
            'billing_interval' => BillingInterval::MONTHLY,
            'status' => SubscriptionPlanStatus::RETIRED,
            'max_workspaces' => 3,
            'max_projects' => 15,
            'max_members' => 8,
        ]);
    }

    protected function createOrganizationWithOwner(User $owner = null): array
    {
        $owner = $owner ?? User::factory()->create();
        
        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Test Org',
            'slug' => 'test-org-' . uniqid(),
            'subscription_plan' => 'free',
            'subscription_status' => 'active',
        ]);
        
        $organization->members()->attach($owner, [
            'role' => OrganizationRole::OWNER->value
        ]);
        
        $freePlan = SubscriptionPlan::where('slug', 'free')->first();
        Subscription::create([
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
        
        $admin = User::factory()->create();
        $member = User::factory()->create();
        
        $organization->members()->attach($admin, ['role' => OrganizationRole::ADMIN->value]);
        $organization->members()->attach($member, ['role' => OrganizationRole::MEMBER->value]);
        
        return [$organization, $owner, $admin, $member];
    }
}

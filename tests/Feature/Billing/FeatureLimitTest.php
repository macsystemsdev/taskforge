<?php

use App\Domain\Billing\Services\FeatureLimitService;
use App\Exceptions\Billing\ProjectLimitReachedException;
use App\Exceptions\Billing\WorkspaceLimitReachedException;
use App\Models\Project;
use App\Models\SubscriptionPlan;
use App\Models\Team;
use App\Models\Workspace;

test('free plan enforces workspace limit', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    
    Workspace::create([
        'organization_id' => $organization->id,
        'name' => 'Workspace 1',
        'slug' => 'workspace-1',
        'description' => 'Test',
        'is_default' => true,
    ]);
    
    expect($organization->canCreateWorkspace())->toBeFalse();
    
    $service = app(FeatureLimitService::class);
    
    $this->expectException(WorkspaceLimitReachedException::class);
    $service->ensureCanCreateWorkspace($organization);
});

test('free plan enforces project limit', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    
    $workspace = Workspace::create([
        'organization_id' => $organization->id,
        'name' => 'Workspace 1',
        'slug' => 'workspace-1',
        'description' => 'Test',
        'is_default' => true,
    ]);
    
    $team = Team::create([
        'workspace_id' => $workspace->id,
        'name' => 'Team 1',
        'slug' => 'team-1',
        'is_personal' => false,
    ]);
    
    for ($i = 1; $i <= 5; $i++) {
        Project::create([
            'workspace_id' => $workspace->id,
            'team_id' => $team->id,
            'name' => "Project $i",
            'slug' => "project-$i",
            'status' => 'active',
            'created_by' => $owner->id,
        ]);
    }
    
    expect($organization->canCreateProject())->toBeFalse();
    
    $service = app(FeatureLimitService::class);
    
    $this->expectException(ProjectLimitReachedException::class);
    $service->ensureCanCreateProject($organization);
});

test('paid plan allows more workspaces', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    $organization->subscription->update([
        'subscription_plan_id' => $proPlan->id,
        'ends_at' => now()->addMonth(),
    ]);
    
    expect($organization->canCreateWorkspace())->toBeTrue();
});

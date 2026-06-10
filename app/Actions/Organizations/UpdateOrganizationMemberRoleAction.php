<?php

namespace App\Actions\Organizations;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

class UpdateOrganizationMemberRoleAction
{

    public function __construct(
        protected CreateActivityLogAction $activity,
    ) {}
    public function handle(
        Organization $organization,
        User $member,
        OrganizationRole $role,
    ): void {

        $membership = $organization
            ->members()
            ->where('users.id', $member->id)
            ->firstOrFail();

        $currentRole = $membership->pivot->role;

        // Prevent demoting the last owner of the organization
        if (
            $currentRole === OrganizationRole::OWNER &&
            $role !== OrganizationRole::OWNER
        ) {

            $ownerCount = $organization
                ->members()
                ->wherePivot(
                    'role',
                    OrganizationRole::OWNER
                )
                ->count();

            if ($ownerCount === 1) {
                throw new \DomainException(
                    'Organization must have at least one owner.'
                );
            }
        }

        // check if user is trying to change their own role and prevent it
        if (
            auth()->id() === $member->id
        ) {
            throw new \DomainException(
                'You cannot change your own role.'
            );
        }

        $organization->members()->updateExistingPivot(
            $member->id,
            [
                'role' => $role,
            ]
        );
        $this->activity->handle(
            subject: $organization,
            event: 'Updated role of {$member->name} to {$role->value} in organization {$organization->name}',
            properties: [
                'member_id' => $member->id,
                'role' => $role->value,
            ]
        );
    }
}

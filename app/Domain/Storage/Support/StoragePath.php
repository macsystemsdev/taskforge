<?php

namespace App\Domain\Storage\Support;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;

class StoragePath
{
    /**
     * Organization shared library.
     */
    public static function organizationLibrary(
        Organization $organization,
    ): string {

        return sprintf(
            'organizations/%s/library',
            $organization->uuid,
        );
    }

    /**
     * Workspace shared library.
     */
    public static function workspaceLibrary(
        Workspace $workspace,
    ): string {

        return sprintf(
            'workspaces/%s/library',
            $workspace->uuid,
        );
    }

    /**
     * Project discussion attachments.
     */
    public static function projectAttachments(
        Project $project,
    ): string {

        return sprintf(
            'projects/%s/attachments',
            $project->uuid,
        );
    }

    /**
     * Project voice notes.
     */
    public static function projectVoiceNotes(
        Project $project,
    ): string {

        return sprintf(
            'projects/%s/voice-notes',
            $project->uuid,
        );
    }

    /**
     * User avatar.
     */
    public static function userAvatar(
        User $user,
    ): string {

        return sprintf(
            'users/%s/avatar',
            $user->uuid,
        );
    }

    /**
     * Team avatar.
     */
    public static function teamAvatar(
        Team $team,
    ): string {

        return sprintf(
            'teams/%s/avatar',
            $team->uuid,
        );
    }

    /**
     * Organization branding.
     */
    public static function organizationLogo(
        Organization $organization,
    ): string {

        return sprintf(
            'organizations/%s/logo',
            $organization->uuid,
        );
    }

    /**
     * Workspace branding.
     */
    public static function workspaceLogo(
        Workspace $workspace,
    ): string {

        return sprintf(
            'workspaces/%s/logo',
            $workspace->uuid,
        );
    }

    /**
     * Temporary uploads.
     *
     * Future:
     * Used before files are permanently attached.
     */
    public static function temporary(): string
    {
        return 'temporary';
    }

    /**
     * Generated exports.
     *
     * Future:
     * CSV, PDF and scheduled reports.
     */
    public static function exports(): string
    {
        return 'exports';
    }
}
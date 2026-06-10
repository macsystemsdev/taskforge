<?php


use App\Models\Project;
use Livewire\Component;

new class extends Component
{
    public Project $project;

    public function render()
    {
        return view(
            'livewire.projects.project-details'
        );
    }
}

?>

<flux:card>

    <flux:heading>
        {{ __('Project Details') }}
    </flux:heading>

    <div class="mt-4 space-y-4">

        <div>
            <p class="tf-muted">
                Project Team
            </p>

            <p class="font-medium">
                {{ $project->team->name }}
            </p>
        </div>
        {{-- <div>
            <p class="tf-muted">
                Leader:
            </p>

            <p class="font-medium">
                {{ $project->team->leader->name }}
            </p>
        </div> --}}

        <div>
            <p class="tf-muted">
                Created By
            </p>

            <p class="font-medium">
                {{ $project->creator->name }}
            </p>
        </div>

        <div>
            <p class="tf-muted">
                Status
            </p>

            <x-ui.status-badge
                :status="$project->status"
            />
        </div>

        <div>
            <p class="tf-muted">
                Created
            </p>

            <p>
                {{ $project->created_at->format('M d, Y') }}
            </p>
        </div>

        <div>
            <p class="tf-muted">
                Due Date
            </p>

            <p>
                {{ $project->due_date?->format('M d, Y') ?? __('No due date') }}
            </p>
        </div>

    </div>

</flux:card>
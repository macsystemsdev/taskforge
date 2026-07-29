<?php

namespace App\View\Components;

use App\Models\SubscriptionPlan;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SubscriptionPlanPreview extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public array $plan,
        public array $metadata = [],
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.subscription-plan-preview');
    }
}

<?php

namespace App\Livewire\Billing;

use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Services\CreateCheckoutService;
use App\Domain\Billing\Services\StartTrialService;
use App\Exceptions\Billing\TrialUnavailableException;
use App\Exceptions\Billing\UnsupportedPaymentProviderException;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BillingDashboard extends Component
{
    public ?Organization $selectedOrganization = null;
    
    public ?SubscriptionPlan $selectedPlan = null;
    
    public PaymentProvider $paymentProvider = PaymentProvider::STRIPE;
    
    public bool $showCheckoutModal = false;
    
    public bool $processingCheckout = false;
    public bool $processingTrial = false;

    public function mount(?Organization $organization = null): void
    {
        if ($organization) {
            Gate::authorize('update', $organization);
            $this->selectedOrganization = $organization->load(['subscription.plan', 'subscription.pendingPlan']);
        }
    }

    #[Computed]
    public function organizations()
    {
        return auth()->user()->organizations()
            ->with(['subscription.plan', 'subscription.pendingPlan'])
            ->get();
    }

    #[Computed]
    public function plans()
    {
        return SubscriptionPlan::query()
            ->purchasable()
            ->orderBy('price')
            ->get();
    }

    #[Computed]
    public function availableProviders(): array
    {
        return [
            [
                'value' => PaymentProvider::STRIPE->value,
                'label' => PaymentProvider::STRIPE->label(),
                'description' => PaymentProvider::STRIPE->description(),
                'available' => PaymentProvider::STRIPE->isSupported(),
            ],
            [
                'value' => PaymentProvider::MTN->value,
                'label' => PaymentProvider::MTN->label(),
                'description' => PaymentProvider::MTN->description(),
                'available' => PaymentProvider::MTN->isSupported(),
            ],
            [
                'value' => PaymentProvider::ORANGE->value,
                'label' => PaymentProvider::ORANGE->label(),
                'description' => PaymentProvider::ORANGE->description(),
                'available' => PaymentProvider::ORANGE->isSupported(),
            ],
        ];
    }

    public function selectOrganization(int $organizationId): void
    {
        $organization = Organization::findOrFail($organizationId);

        Gate::authorize('update', $organization);
        
        $this->selectedOrganization = $organization->load(['subscription.plan', 'subscription.pendingPlan']);
    }

    public function selectPlan(SubscriptionPlan $plan): void
    {
        if (!$this->selectedOrganization) {
            Flux::toast(
                variant: 'warning',
                text: __('Please select an organization first.'),
            );
            return;
        }

        Gate::authorize('update', $this->selectedOrganization);

        abort_unless($plan->isPurchasable(), 404);

        $this->selectedPlan = $plan;
        $this->showCheckoutModal = true;
    }

    public function closeCheckoutModal(): void
    {
        $this->selectedPlan = null;
        $this->showCheckoutModal = false;
        $this->paymentProvider = PaymentProvider::STRIPE;
    }

    public function confirmPlanChange(): mixed
    {
        if (!$this->selectedOrganization) {
            Flux::toast(
                variant: 'warning',
                text: __('Please select an organization first.'),
            );
            return null;
        }

        Gate::authorize('update', $this->selectedOrganization);

        if (!$this->selectedPlan) {
            return null;
        }

        $this->processingCheckout = true;

        try {
            $plan = SubscriptionPlan::query()
                ->purchasable()
                ->findOrFail($this->selectedPlan->id);

            if (!$this->paymentProvider->isSupported()) {
                throw new UnsupportedPaymentProviderException(
                    "Payment provider '{$this->paymentProvider->value}' is not available."
                );
            }

            $response = app(CreateCheckoutService::class)->handle(
                new CheckoutData(
                    organization: $this->selectedOrganization,
                    plan: $plan,
                    provider: $this->paymentProvider,
                )
            );

            return redirect()->away($response->url);
        } catch (\Exception $e) {
            Flux::toast(
                variant: 'danger',
                text: $e->getMessage(),
            );
            return null;
        } finally {
            $this->processingCheckout = false;
        }
    }

    public function startTrial(StartTrialService $service): void
    {
        if (!$this->selectedOrganization) {
            Flux::toast(
                variant: 'warning',
                text: __('Please select an organization first.'),
            );
            return;
        }

        Gate::authorize('update', $this->selectedOrganization);

        $this->processingTrial = true;

        try {
            $service->handle($this->selectedOrganization->subscription);

            $this->selectedOrganization->refresh();

            Flux::toast(
                variant: 'success',
                text: __('Your free trial has started.'),
            );
        } catch (TrialUnavailableException) {
            Flux::toast(
                variant: 'error',
                text: __('Free trial is no longer available.'),
            );
        } finally {
            $this->processingTrial = false;
        }
    }

    public function render()
    {
        return view('livewire.billing.billing-dashboard');
    }
}

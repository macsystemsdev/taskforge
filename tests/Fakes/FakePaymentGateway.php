<?php

namespace Tests\Fakes;

use App\Contracts\Billing\PaymentGateway;
use App\Models\Organization;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;

class FakePaymentGateway implements PaymentGateway
{
    public array $createdCustomers = [];
    public array $createdCheckouts = [];
    public array $chargedTransactions = [];
    public bool $shouldFail = false;
    public string $failureReason = 'Payment failed';
    public ?string $providerReference = 'fake_session_123';

    public function createCustomer(Organization $organization): string
    {
        if ($this->shouldFail) {
            throw new \Exception($this->failureReason);
        }
        
        $customerId = 'cus_fake_' . $organization->id;
        $this->createdCustomers[] = $customerId;
        return $customerId;
    }

    public function createCheckout(Organization $organization, SubscriptionPlan $plan, PaymentTransaction $transaction): array
    {
        if ($this->shouldFail) {
            throw new \Exception($this->failureReason);
        }
        
        $checkout = [
            'id' => $this->providerReference,
            'url' => 'https://fake-checkout.example.com/' . $transaction->id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
        ];
        
        $this->createdCheckouts[] = $checkout;
        return $checkout;
    }

    public function chargeCustomer(PaymentTransaction $transaction): bool
    {
        if ($this->shouldFail) {
            throw new \Exception($this->failureReason);
        }
        
        $this->chargedTransactions[] = $transaction->id;
        return true;
    }

    public function getProvider(): string
    {
        return 'stripe';
    }
}

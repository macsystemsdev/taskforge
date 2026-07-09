<?php

namespace App\Models;

use App\Domain\Billing\Enum\PaymentProvider;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('webhook_events')]
#[Fillable(['provider', 'event_id', 'event_type', 'processed_at'])]

class WebhookEvent extends Model
{
    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }

    protected function alreadyProcessed($event): bool
    {
        return WebhookEvent::query()
            ->where(
                'event_id',
                $event->id
            )
            ->exists();
    }

    protected function storeWebhookEvent($event): void
    {
        WebhookEvent::create([
            'provider' => PaymentProvider::STRIPE,
            'event_id' => $event->id,
            'event_type' => $event->type,
            'processed_at' => now(),
        ]);
    }
}

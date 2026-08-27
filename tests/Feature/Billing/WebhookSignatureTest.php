<?php

use App\Models\WebhookEvent;

test('webhook with invalid signature is rejected', function () {
    $payload = json_encode([
        'id' => 'evt_test_invalid',
        'type' => 'checkout.session.completed',
    ]);
    
    $response = $this->postJson(
        route('stripe.webhook'),
        json_decode($payload, true),
        ['Stripe-Signature' => 'invalid_signature_123']
    );
    
    expect($response->status())->toBe(400);
});

test('webhook with missing signature is rejected', function () {
    $payload = json_encode([
        'id' => 'evt_test_missing',
        'type' => 'checkout.session.completed',
    ]);
    
    $response = $this->postJson(
        route('stripe.webhook'),
        json_decode($payload, true),
        []
    );
    
    expect($response->status())->toBe(400);
});

test('webhook with tampered signature is rejected', function () {
    $payload = json_encode([
        'id' => 'evt_test_tampered',
        'type' => 'checkout.session.completed',
    ]);
    
    // Use a random but properly formatted signature
    $tamperedSignature = 't=' . time() . ',v1=abcdef1234567890';
    
    $response = $this->postJson(
        route('stripe.webhook'),
        json_decode($payload, true),
        ['Stripe-Signature' => $tamperedSignature]
    );
    
    expect($response->status())->toBe(400);
});

test('webhook events are stored with unique event IDs', function () {
    $eventId = 'evt_unique_test';
    
    WebhookEvent::create([
        'provider' => 'stripe',
        'event_id' => $eventId,
        'event_type' => 'checkout.session.completed',
        'processed_at' => now(),
    ]);
    
    // Duplicate should fail
    $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
    
    WebhookEvent::create([
        'provider' => 'stripe',
        'event_id' => $eventId,
        'event_type' => 'checkout.session.completed',
        'processed_at' => now(),
    ]);
});

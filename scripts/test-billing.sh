#!/bin/bash

echo "🧪 Running Billing Tests"
echo "========================"

docker compose exec app php artisan optimize:clear

echo "🔐 Authorization Tests:"
docker compose exec app php artisan test tests/Feature/Billing/BillingAuthorizationTest.php --compact

echo "🏢 Organization Isolation Tests:"
docker compose exec app php artisan test tests/Feature/Billing/OrganizationIsolationTest.php --compact

echo "📦 Plan Tests:"
docker compose exec app php artisan test tests/Feature/Billing/PlanTest.php --compact

echo "🎯 Trial Tests:"
docker compose exec app php artisan test tests/Feature/Billing/TrialTest.php --compact

echo "💳 Payment Tests:"
docker compose exec app php artisan test tests/Feature/Billing/CompletePaymentTest.php --compact

echo "🔀 Payment State Machine Tests:"
docker compose exec app php artisan test tests/Feature/Billing/PaymentStateMachineTest.php --compact

echo "💰 Amount Tests:"
docker compose exec app php artisan test tests/Unit/Billing/PaymentAmountTest.php --compact

echo "❌ Checkout Failure Tests:"
docker compose exec app php artisan test tests/Feature/Billing/CheckoutFailureTest.php --compact

echo "🔄 Webhook Tests:"
docker compose exec app php artisan test tests/Feature/Billing/WebhookTest.php --compact

echo "👤 Stripe Customer Tests:"
docker compose exec app php artisan test tests/Feature/Billing/StripeCustomerTest.php --compact

echo "🔒 Stripe Customer Isolation Tests:"
docker compose exec app php artisan test tests/Feature/Billing/StripeCustomerIsolationTest.php --compact

echo "📅 Subscription Lifecycle Tests:"
docker compose exec app php artisan test tests/Feature/Billing/SubscriptionLifecycleTest.php --compact

echo "🚫 Feature Limit Tests:"
docker compose exec app php artisan test tests/Feature/Billing/FeatureLimitTest.php --compact

echo "✅ Complete"

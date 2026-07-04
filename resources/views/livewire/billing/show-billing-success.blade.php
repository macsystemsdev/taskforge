
<div class="mx-auto max-w-2xl">

    <x-ui.card>

        <div class="text-center">

            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-500/10">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-8 w-8 text-blue-600 dark:text-blue-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />

                </svg>

            </div>

            <h1 class="mt-6 text-2xl font-semibold text-zinc-950 dark:text-white">
                Payment Submitted
            </h1>

            <p class="mt-3 tf-muted">
                We're confirming your payment with Stripe.
                Your subscription will be updated automatically once payment has been verified.
            </p>

            <div class="mt-8 rounded-lg border border-blue-200 bg-blue-50 p-4 text-left dark:border-blue-900 dark:bg-blue-950/20">

                <p class="text-sm text-blue-900 dark:text-blue-200">

                    You may safely close this page or return to your dashboard.
                    Once Stripe confirms the payment, your subscription will be updated automatically.

                </p>

            </div>

            <div class="mt-8">

                <a href="{{ route('dashboard') }}"
                    class="tf-button-primary">

                    Return to Dashboard

                </a>

            </div>

        </div>

    </x-ui.card>

</div>
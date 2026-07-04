
<div class="mx-auto max-w-2xl">

    <x-ui.card>

        <div class="text-center">

            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/10">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-8 w-8 text-amber-600 dark:text-amber-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4m0 4h.01M22 12A10 10 0 112 12a10 10 0 0120 0z" />

                </svg>

            </div>

            <h1 class="mt-6 text-2xl font-semibold">
                Payment Cancelled
            </h1>

            <p class="mt-3 tf-muted">
                Your payment was cancelled before completion.
            </p>

            <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-4 text-left">

                <p class="text-sm text-amber-800">

                    No payment was processed and your current subscription remains active.
                    You can return to billing and try again whenever you're ready.

                </p>

            </div>

            <div class="mt-8 flex justify-center gap-3">

                <a
                    href="{{ route('dashboard') }}"
                    class="tf-button-secondary">

                    Dashboard

                </a>

                <a
                    href="{{ route('organizations.billing', $organization ?? null) }}"
                    class="tf-button-primary">

                    Back to Billing

                </a>

            </div>

        </div>

    </x-ui.card>

</div>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    {{-- Navigation --}}
    <nav class="fixed top-0 z-50 w-full border-b border-zinc-200 bg-white/90 backdrop-blur dark:border-white/10 dark:bg-zinc-950/90">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <img src="/favicon.svg" alt="TaskForge" class="size-8" />
                <span class="text-lg font-semibold dark:text-white">TaskForge</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition">Log in</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-zinc-900 px-5 py-2 text-sm font-semibold text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200 transition">Get Started</a>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <main class="pt-32 pb-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Hero Text --}}
            <div class="mx-auto max-w-3xl text-center">
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 mb-6">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Free 14-day trial · No credit card required
                </div>

                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl dark:text-white">
                    Your team's work,{" "}
                    <span class="text-indigo-600">organized</span>
                </h1>

                <p class="mt-6 text-lg leading-8 text-zinc-600 sm:text-xl dark:text-zinc-400">
                    TaskForge brings your projects, tasks, and team communication into one place. Create organizations, manage workspaces, assign work, and stay on top of deadlines — without the chaos.
                </p>

                <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:justify-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-8 py-4 text-base font-semibold text-white hover:bg-zinc-700 transition">
                        Start Free Trial
                        <span class="ml-2">→</span>
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-8 py-4 text-base font-semibold text-zinc-900 hover:bg-zinc-50 transition">
                        Sign In
                    </a>
                </div>
            </div>

            {{-- Feature Grid --}}
            <div class="mt-24 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-6 dark:border-white/10 dark:bg-white/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold dark:text-white">Organizations</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Run multiple organizations, each with its own workspaces, teams, and members.</p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-6 dark:border-white/10 dark:bg-white/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-violet-100 text-violet-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold dark:text-white">Projects & Tasks</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Create projects, assign tasks, set deadlines, and track progress in real time.</p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-6 dark:border-white/10 dark:bg-white/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold dark:text-white">Team Collaboration</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Invite members, assign roles, and collaborate with comments and file sharing.</p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-6 dark:border-white/10 dark:bg-white/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold dark:text-white">Subscription Plans</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Start with a free plan, upgrade as you grow, and manage billing per organization.</p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-6 dark:border-white/10 dark:bg-white/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-cyan-100 text-cyan-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold dark:text-white">Reports & Insights</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">See what's moving, what's stuck, and where your team needs attention.</p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-6 dark:border-white/10 dark:bg-white/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-rose-100 text-rose-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold dark:text-white">Secure & Reliable</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Role-based access control, owner-controlled billing, and secure payment processing.</p>
                </div>
            </div>

            {{-- Bottom CTA --}}
            <div class="mt-24 text-center">
                <h2 class="text-3xl font-bold sm:text-4xl dark:text-white">Ready to get organized?</h2>
                <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">Create your free account and start managing work with clarity.</p>
                <a href="{{ route('register') }}" class="mt-8 inline-flex items-center justify-center rounded-lg bg-zinc-900 px-10 py-4 text-base font-semibold text-white hover:bg-zinc-700 transition">
                    Get Started
                    <span class="ml-2">→</span>
                </a>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-zinc-200 py-8 dark:border-white/10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center text-sm text-zinc-500">
            <a href="{{ route('about') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300">About</a>
            <span class="mx-2">·</span>
            &copy; {{ date('Y') }} TaskForge. All rights reserved.
        </div>
    </footer>
    @fluxScripts
</body>
</html>

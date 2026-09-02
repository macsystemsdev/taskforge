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
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="/favicon.svg" alt="TaskForge" class="size-8" />
                    <span class="text-lg font-semibold dark:text-white">TaskForge</span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition">Log in</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-zinc-900 px-5 py-2 text-sm font-semibold text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200 transition">Get Started</a>
            </div>
        </div>
    </nav>

    {{-- About Content --}}
    <main class="pt-32 pb-20">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl dark:text-white">About TaskForge</h1>
                <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">Built for teams who want clarity, not chaos.</p>
            </div>

            <div class="space-y-12">
                {{-- The Problem --}}
                <section>
                    <h2 class="text-2xl font-bold dark:text-white mb-4">The Problem</h2>
                    <p class="text-zinc-600 dark:text-zinc-400 leading-8">
                        Teams today juggle multiple tools — one for projects, another for tasks, another for communication, and yet another for billing. This creates fragmented workflows, lost context, and confusion about who's doing what.
                    </p>
                    <p class="mt-4 text-zinc-600 dark:text-zinc-400 leading-8">
                        Organizations with multiple teams face an even bigger challenge: keeping every workspace organized while managing subscriptions and member access across departments.
                    </p>
                </section>

                {{-- The Solution --}}
                <section>
                    <h2 class="text-2xl font-bold dark:text-white mb-4">The Solution</h2>
                    <p class="text-zinc-600 dark:text-zinc-400 leading-8">
                        TaskForge brings everything together — organizations, workspaces, teams, projects, tasks, comments, files, and billing — in one place.
                    </p>
                    <p class="mt-4 text-zinc-600 dark:text-zinc-400 leading-8">
                        Whether you're a small startup or a growing organization with multiple teams, TaskForge scales with you. Start with a free plan, upgrade when you need more, and never lose sight of your work.
                    </p>
                </section>

                {{-- What Makes Us Different --}}
                <section>
                    <h2 class="text-2xl font-bold dark:text-white mb-4">What Makes TaskForge Different</h2>
                    <ul class="space-y-4">
                        <li class="flex gap-3">
                            <span class="mt-1 text-emerald-500">✓</span>
                            <div>
                                <p class="font-semibold dark:text-white">Multi-Organization Support</p>
                                <p class="text-zinc-600 dark:text-zinc-400">Run multiple organizations from one account, each with its own workspaces, teams, members, and subscription plans.</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-1 text-emerald-500">✓</span>
                            <div>
                                <p class="font-semibold dark:text-white">Built-in Billing</p>
                                <p class="text-zinc-600 dark:text-zinc-400">Manage plans, trials, and subscriptions per organization without leaving the app.</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-1 text-emerald-500">✓</span>
                            <div>
                                <p class="font-semibold dark:text-white">Role-Based Access</p>
                                <p class="text-zinc-600 dark:text-zinc-400">Owners, admins, and members each have clear permissions. Billing is owner-controlled.</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-1 text-emerald-500">✓</span>
                            <div>
                                <p class="font-semibold dark:text-white">Real-Time Collaboration</p>
                                <p class="text-zinc-600 dark:text-zinc-400">Comments, file sharing, presence indicators, and notifications keep everyone in sync.</p>
                            </div>
                        </li>
                    </ul>
                </section>

                {{-- CTA --}}
                <div class="text-center pt-8">
                    <h2 class="text-2xl font-bold dark:text-white mb-4">Ready to get started?</h2>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-8 py-4 text-base font-semibold text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200 transition">
                        Create Free Account
                        <span class="ml-2">→</span>
                    </a>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-zinc-200 py-8 dark:border-white/10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('home') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300">Home</a>
            <span class="mx-2">·</span>
            <a href="{{ route('about') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300">About</a>
            <span class="mx-2">·</span>
            &copy; {{ date('Y') }} TaskForge. All rights reserved.
        </div>
    </footer>
</body>
</html>

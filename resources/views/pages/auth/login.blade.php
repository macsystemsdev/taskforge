@extends('layouts.auth-simple')

@section('title', __('Log in'))

@section('content')
    <div class="flex flex-col gap-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-zinc-950 dark:text-white">{{ __('Welcome back') }}</h1>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Sign in to manage your organizations, projects, and tasks.') }}</p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <div>
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <div class="mt-2 text-right">
                        <flux:link class="text-sm" :href="route('password.request')" wire:navigate>
                            {{ __('Forgot password?') }}
                        </flux:link>
                    </div>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Keep me signed in')" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                {{ __('Sign in') }}
            </flux:button>
        </form>

        @if (Route::has('register'))
            <p class="text-sm text-center text-zinc-600 dark:text-zinc-400">
                {{ __('New to TaskForge?') }}
                <flux:link :href="route('register')" wire:navigate>{{ __('Create an account') }}</flux:link>
            </p>
        @endif
    </div>
@endsection

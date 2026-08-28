@extends('layouts.auth-simple')

@section('title', __('Register'))

@section('content')
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create your TaskForge account')" :description="__('Start organizing your team, projects, and tasks.')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4">
            @csrf

            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="John Doe"
            />

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                viewable
            />

            <flux:input
                name="password_confirmation"
                :label="__('Confirm Password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                viewable
            />

            <div class="flex items-center justify-end mt-2">
                <flux:button variant="primary" type="submit" class="w-full" data-test="register-button">
                    {{ __('Create Account') }}
                </flux:button>
            </div>
        </form>

        @if (Route::has('login'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Already have an account?') }}</span>
                <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
            </div>
        @endif
    </div>
@endsection
